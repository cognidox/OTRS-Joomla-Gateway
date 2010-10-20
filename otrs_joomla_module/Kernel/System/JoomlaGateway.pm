# --
# Kernel/System/JoomlaGateway.pm - functions used by the Joomla gateway
# Copyright (c) 2010 Cognidox Ltd
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in
# all copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
# THE SOFTWARE.
# --

use lib qw(../../cpan-lib);

package Kernel::System::JoomlaGateway;

use Kernel::Config;
use Kernel::System::CustomerAuth;
use Kernel::System::CustomerUser;
use Kernel::System::DB;
use Kernel::System::Encode;
use Kernel::Output::HTML::Layout;
use Kernel::System::Log;
use Kernel::System::Main;
use Kernel::System::State;
use Kernel::System::Priority;
use Kernel::System::SystemAddress;
use Kernel::System::Ticket;
use Kernel::System::Time;
use Kernel::System::Valid;
use Kernel::System::Web::Request;

use Data::Dumper;

use strict;
use warnings;
use MIME::Base64 qw(decode_base64);

sub new {
    my ( $Type, %Param ) = @_;

    # allocate new hash for object
    my $Self = {};
    bless( $Self, $Type );

    # 0=off; 1=on;
    $Self->{Debug} = $Param{Debug} || 0;

    # Get needed objects
    for my $Object (qw(ConfigObject LogObject TimeObject DBObject TicketObject
            MainObject EncodeObject TicketObject CustomerUserObject
            QueueObject)) {
        if ( $Param{$Object} ) {
            $Self->{$Object} = $Param{$Object};
        } else {
            die "Got no $Object!";
        }
    }
    $Self->{'StateObject'} = Kernel::System::State->new(%{$Self}, %Param);
    $Self->{'SystemAddress'} = Kernel::System::SystemAddress->new(%{$Self}, %Param);
    $Self->{'ParamObject'} = Kernel::System::Web::Request->new(%{$Self}, %Param);
    $Self->{'LayoutObject'} = Kernel::Output::HTML::Layout->new(%{$Self}, %Param);

    # Old versions of OTRS do not provide a CustomerCompany object,
    # so care has to be taken here
    if ( $Self->{MainObject}->Require("Kernel::System::CustomerCompany") ) {
        $Self->{'CustomerCompanyObject'} = Kernel::System::CustomerCompany->new(%{$Self}, %Param );
    }

    return $Self;
}

# Look up ticket information for the RPC interface
sub TicketSearch {
    my ( $Self, %Param ) = @_;

    # Look up the basic ticket list
    my @ids = $Self->{'TicketObject'}->TicketSearch(%Param);
    if (!scalar(@ids)) { return undef; }

    # Now, gather some information about the tickets
    my @ticketData;
    my %customerCache;
    my @CustomerArticleTypes = $Self->{TicketObject}->ArticleTypeList(Type => 'Customer');
    foreach my $id (@ids) {
        my %article = $Self->{TicketObject}->ArticleLastCustomerArticle(TicketID => $id);
        if (!%article) {
            my @idx = $Self->{TicketObject}->ArticleIndex(TicketID => $id);
            next unless (scalar(@idx));
            %article = $Self->{TicketObject}->ArticleGet(ArticleID => $idx[0]);
        }
        next unless (%article);
        
        # As we don't want all of the ticket, and want to keep the RPC
        # interface fairly quick, we'll only grab a few pieces of the 
        # ticket data
        my %ticketData;
        foreach (qw(Age PriorityID StateID Changed ArticleID QueueID 
                    TicketID CustomerUserID CustomerID Priority Queue
                    State Title Created CreateTimeUnix)) {
            $ticketData{$_} = $article{$_};
        }
        $ticketData{'TicketNumber'} = $article{'TicketNumber'} . " ";
        if ($ticketData{'CustomerID'} && 
            exists $Self->{'CustomerCompanyObject'}) {
            if (exists $customerCache{$ticketData{'CustomerID'}}) {
                $ticketData{'CompanyName'} = $customerCache{$ticketData{'CustomerID'}};
            } else {
                my %company = $Self->{'CustomerCompanyObject'}->CustomerCompanyGet(CustomerID => $ticketData{'CustomerID'});
                if (%company) {
                    $ticketData{'CompanyName'} = $ticketData{'CompanyName'} = 
                        $company{'CustomerCompanyName'};
                }
            }
        }

        # Include the description of ticket
        if (exists $Param{'IncludeDescription'} && $Param{'IncludeDescription'}) {
            # Get all the articles attached to the ticket
            my @articles = $Self->{TicketObject}->ArticleContentIndex(TicketID => $id, ArticleType => \@CustomerArticleTypes, StripPlainBodyAsAttachment => 2);
            my $desc = shift(@articles);
            $ticketData{'Description'} = $desc->{'Body'};
        }

        push(@ticketData, { %ticketData });
    }
    return [ @ticketData ];
}

# Get the queues available
sub GetTicketQueues {
    my ( $Self, %Param ) = @_;
    my %NewTos = ( '', '' );
    my $Module = $Self->{ConfigObject}->Get('CustomerPanel::NewTicketQueueSelectionModule')
        || 'Kernel::Output::HTML::CustomerNewTicketQueueSelectionGeneric';
    if ( $Self->{MainObject}->Require($Module) ) {
        $Self->{UserID} = $Param{'CustomerUserID'};
        my $Object = $Module->new(%{$Self});
        %NewTos = ( $Object->Run( Env => $Self ), ( '', => '' ) );
    }
    if (%NewTos) {
        for ( keys %NewTos ) {
            $NewTos{"$_||$NewTos{$_}"} = $NewTos{$_};
            delete $NewTos{$_};
        }
    }
    return \%NewTos;
}

# Want to save any un-needed calls to lookup the customer, so return the
# customer(s) of the user here
sub GetCustomerUserData {
    my ( $Self, %Param ) = @_;
    my %user = $Self->{'CustomerUserObject'}->CustomerSearch(%Param);
    if (!%user) {
        return undef;
    }
    my @loginData = keys(%user);
    if (!scalar(@loginData)) { return undef; }
    my $login = $loginData[0];

    # Now, get the customer ID
    my @customerIDs = $Self->{'CustomerUserObject'}->CustomerIDs(%Param, User => $login);

    return [ $login, [ @customerIDs ] ];
}

# Authenticate a user
sub AuthenticateOTRSUser {
    my ( $Self, %Param ) = @_;
    my $CustomerAuthObject = Kernel::System::CustomerAuth->new(%{$Self}, %Param);
    if ($CustomerAuthObject->Auth( %Param )) {
        my %userData = $Self->{'CustomerUserObject'}->CustomerUserDataGet( User => $Param{'User'} );
        if (%userData) {
            return [ $userData{'UserEmail'}, 
                     $userData{'UserFirstname'} . ' ' . $userData{'UserLastname'} ];
        }
    }
    return undef;
}

# Return a ticket
sub GetTicket {
    my ( $Self, %Param ) = @_;
    if (!$Self->{TicketObject}->CustomerPermission(
            Type => 'ro',
            TicketID => $Param{'TicketID'},
            UserID => $Param{'CustomerUserID'})) {
        return undef;
    }
    my %ticket = $Self->{TicketObject}->TicketGet(TicketID => $Param{TicketID});
    if (!%ticket) {
        return undef;
    }

    # Gather the basic information needed for the ticket display
    my %ticketData;
    foreach (qw(Changed Created TicketID CustomerUserID CustomerID Queue
                State StateType Title Priority PriorityID StateID)) {
        $ticketData{$_} = $ticket{$_};
    }
    $ticketData{'TicketNumber'} = $ticket{'TicketNumber'} . ' ';
    $ticketData{'ArticleIndex'} = [];
    $ticketData{'Attachments'} = [];

    # Get all the articles attached to the ticket
    my @CustomerArticleTypes = $Self->{TicketObject}->ArticleTypeList(Type => 'Customer');
    my @articles = $Self->{TicketObject}->ArticleContentIndex(TicketID => $Param{TicketID}, ArticleType => \@CustomerArticleTypes, StripPlainBodyAsAttachment => 2);

    # Go through the articles, processing
    # Need the submitter, the submit date, the body of the content,
    # the encoding of the content
    foreach my $a (@articles) {
        my $item = {};
        $item->{'From'} = $a->{'From'};
        $item->{'Created'} = $a->{'Created'};
        $item->{'ArticleID'} = $a->{'ArticleID'};
        $item->{'Type'} = 'text/plain';
        $item->{'Body'} = '';

        if (exists $a->{'Atms'}) {
            $item->{'Atms'} = $a->{'Atms'};
            if (exists $a->{'AttachmentIDOfHTMLBody'} &&
                $a->{'AttachmentIDOfHTMLBody'}) {
                # Get the attachment
                my $att = $a->{'Atms'}->{$a->{'AttachmentIDOfHTMLBody'}};
                if ($att && exists $att->{'ContentType'} &&
                    $att->{'ContentType'} =~ m#^text/x?html#i) {
                    $item->{'Type'} = 'text/html';
                    my %attData = $Self->{TicketObject}->ArticleAttachment(
                                    ArticleID => $a->{'ArticleID'},
                                    FileID => $a->{'AttachmentIDOfHTMLBody'});
                    if (%attData) {
                        $item->{'Body'} = $attData{'Content'};
                        $item->{'Type'} = 'text/html';
                    }
                } else {
                    $item->{'Body'} = $a->{'Body'};
                }
                delete($item->{'Atms'}->{$a->{'AttachmentIDOfHTMLBody'}});
            } else {
                $item->{'Body'} = $a->{'Body'};
            }
            if (scalar(keys(%{$item->{'Atms'}}))) {
                push(@{$ticketData{'Attachments'}},
                        { 'ArticleID' => $a->{'ArticleID'},
                          'Atms' => $item->{'Atms'} });
            }
            delete($item->{'Atms'});
            push(@{$ticketData{'ArticleIndex'}}, $item);
        }
    }
    $ticketData{'Main'} = shift(@{$ticketData{'ArticleIndex'}});

    return { %ticketData };
}

# Reply to a ticket
# Return a HASH containing an error message
sub TicketReply {
    my ( $Self, %Param ) = @_;
    $Self->{Config} = $Self->{ConfigObject}->Get('Ticket::Frontend::CustomerTicketZoom');
    if (!$Self->{TicketObject}->CustomerPermission(
            Type => 'rw',
            TicketID => $Param{'TicketID'},
            UserID => $Param{'CustomerUserID'})) {
        return { 'error' => 'Permission denied' };
    }
    my %Ticket = $Self->{TicketObject}->TicketGet(TicketID => $Param{'TicketID'}); 
    my $FollowUpPossible = $Self->{QueueObject}->GetFollowUpOption(
            QueueID => $Ticket{QueueID});
    my $Lock = $Self->{QueueObject}->GetFollowUpLockOption(
            QueueID => $Ticket{QueueID} );
    my %State = $Self->{StateObject}->StateGet(ID => $Ticket{StateID});
    if ($FollowUpPossible =~ /(new ticket|reject)/i && 
        $State{TypeName} =~ /^close/i ) {
        return { 'error' => 'Can\'t reopen ticket, not possible in this queue' };
    }


    # Get the user details
    my %userData = $Self->{'CustomerUserObject'}->CustomerUserDataGet( User => $Param{'CustomerUserID'} );
    if (!%userData) {
        return { 'error' => 'Permission denied' };
    }
    my $From = $userData{'UserFirstname'} . ' ' . $userData{'UserLastname'} .
               ' <' . $userData{'UserEmail'} . '>';
    my $mime = "text/html";

    $Param{Body} = $Self->{LayoutObject}->RichTextDocumentComplete(
                    String => $Param{Body});
    my $ArticleID = $Self->{TicketObject}->ArticleCreate(
                        TicketID    => $Ticket{TicketID},
                        ArticleType => 'webrequest',
                        SenderType  => 'customer',
                        From        => $From,
                        Subject     => $Ticket{Title},
                        Body        => $Param{Body},
                        MimeType    => $mime,
                        Charset     => 'utf-8',
                        UserID      => $Self->{ConfigObject}->Get('CustomerPanelUserID'),
                        OrigHeader  => {
                            From    => $From,
                            To      => 'System',
                            Subject => $Ticket{Title},
                            Body    => $Self->{LayoutObject}->RichText2Ascii( String => $Param{Body} ),
                        },
                        HistoryType      => 'FollowUp',
                        HistoryComment   => $Self->{Config}->{HistoryComment} || '%%',
                        AutoResponseType => 'auto follow up',
                    );
    if ($ArticleID) {
        my %NextStateData = $Self->{StateObject}->StateGet( ID => $Param{StateID} );
        my $NextState = $NextStateData{Name}
            || $Self->{Config}->{StateDefault}
            || 'open';
        $Self->{TicketObject}->StateSet(
            TicketID  => $Ticket{TicketID},
            ArticleID => $ArticleID,
            State     => $NextState,
            UserID    => $Self->{ConfigObject}->Get('CustomerPanelUserID'),
        );
        if ($Self->{Config}->{Priority} && $Param{PriorityID}) {
            $Self->{TicketObject}->PrioritySet(
                TicketID   => $Ticket{TicketID},
                PriorityID => $Param{PriorityID},
                UserID     => $Self->{ConfigObject}->Get('CustomerPanelUserID'),
            );
        }
        if (exists($Param{'Attachments'}) && ref($Param{'Attachments'}) eq 'ARRAY') {
            foreach my $a (@{$Param{'Attachments'}}) {
                next unless ($a->{'name'});
                my %aData = ( 'Content' => decode_base64($a->{'content'}),
                              'Filename' => $a->{'name'},
                              'ContentType' => $a->{'type'},
                              'ArticleID' => $ArticleID,
                              'UserID' => $Self->{ConfigObject}->Get('CustomerPanelUserID') );
                $Self->{TicketObject}->ArticleWriteAttachment(%aData);

            }
        }
        return {};
    } else {
        return { 'error' => 'Unable to add reply to ticket' };
    }
}

sub TicketSubmit {
    my ( $Self, %Param ) = @_;
    $Self->{Config} = $Self->{ConfigObject}->Get('Ticket::Frontend::CustomerTicketMessage');
    my ( $NewQueueID, $To ) = split( /\|\|/, $Param{'Dest'} );
    if ( !$To ) {
        return { 'error' => 'Invalid queue' };
    }
    if ( !$Param{'Subject'} )
    {
        return { 'error' => 'Invalid subject' };
    }
    if ( !$Param{'Body'} )
    {
        return { 'error' => 'Invalid message' };
    }
    if ( !$Self->{Config}->{Priority} ) {
        $Param{PriorityID} = '';
        $Param{Priority}   = $Self->{Config}->{PriorityDefault};
    }

    # Get the user details
    my %userData = $Self->{'CustomerUserObject'}->CustomerUserDataGet( User => $Param{'CustomerUserID'} );
    if (!%userData) {
        return { 'error' => 'Permission denied' };
    }
    my $From = $userData{'UserFirstname'} . ' ' . $userData{'UserLastname'} .
               ' <' . $userData{'UserEmail'} . '>';

    my $TicketID = $Self->{TicketObject}->TicketCreate(
            QueueID      => $NewQueueID,
            Title        => $Param{Subject},
            PriorityID   => $Param{PriorityID},
            Priority     => $Param{Priority},
            Lock         => 'unlock',
            State        => $Self->{Config}->{StateDefault},
            CustomerID   => $Param{'CustomerID'},
            CustomerUser => $Param{'CustomerUserID'},
            OwnerID      => $Self->{ConfigObject}->Get('CustomerPanelUserID'),
            UserID       => $Self->{ConfigObject}->Get('CustomerPanelUserID'),
        );

    if ($TicketID) {
        my $ArticleID = $Self->{TicketObject}->ArticleCreate(
            TicketID         => $TicketID,
            ArticleType      => $Self->{Config}->{ArticleType},
            SenderType       => $Self->{Config}->{SenderType},
            From             => $From,
            To               => $To,
            Subject          => $Param{Subject},
            Body             => $Self->{LayoutObject}->RichTextDocumentComplete(
                String => $Param{Body}),
            MimeType         => 'text/html',
            Charset          => 'utf-8',
            UserID           => $Self->{ConfigObject}->Get('CustomerPanelUserID'
),
            HistoryType      => $Self->{Config}->{HistoryType},
            HistoryComment   => $Self->{Config}->{HistoryComment} || '%%',
            AutoResponseType => 'auto reply',
            OrigHeader       => {
                From    => $From,
                To      => 'System',
                Subject => $Param{Subject},
                Body    => $Self->{LayoutObject}->RichText2Ascii( String => $Param{Body} ),
            },
            Queue => $Self->{QueueObject}->QueueLookup( QueueID => $NewQueueID ),
        );
        if ($ArticleID) {
            if (exists($Param{'Attachments'}) && ref($Param{'Attachments'}) eq 'ARRAY') {
                foreach my $a (@{$Param{'Attachments'}}) {
                    next unless ($a->{'name'});
                    my %aData = ( 'Content' => decode_base64($a->{'content'}),
                                  'Filename' => $a->{'name'},
                                  'ContentType' => $a->{'type'},
                                  'ArticleID' => $ArticleID,
                                  'UserID' => $Self->{ConfigObject}->Get('CustomerPanelUserID') );
                    $Self->{TicketObject}->ArticleWriteAttachment(%aData);

                }
            }
            return { 'id' => $TicketID };
        }
    }
    return { 'error' => 'Unable to create ticket' };
}

# Return a ticket priority list
sub PriorityList {
    my ( $Self, %Param ) = @_;
    if ( !$Param{CustomerUserID} ) {
        return undef;
    }
    if ( $Param{'NewTicket'} ) {
        $Self->{Config} = $Self->{ConfigObject}->Get('Ticket::Frontend::CustomerTicketMessage');
    } else {
        $Self->{Config} = $Self->{ConfigObject}->Get('Ticket::Frontend::CustomerTicketZoom');
    }

    # Need to know if the user can even set priorities
    if ( !$Self->{Config}->{'Priority'} )
    {
        return undef;
    }

    my $PriorityObject = Kernel::System::Priority->new( %{$Self}, %Param );
    my %Data = $PriorityObject->PriorityList(%Param);
    my $ACL = $Self->{'TicketObject'}->TicketAcl(
        %Param,
        ReturnType    => 'Ticket',
        ReturnSubType => 'Priority',
        Data          => \%Data,
    );
    if ($ACL) {
        return $Self->{'TicketObject'}->TicketAclData();
    }
    return %Data;
}

sub PriorityDefault {
    my ( $Self, %Param ) = @_;
    $Self->{Config} = $Self->{ConfigObject}->Get('Ticket::Frontend::CustomerTicketMessage');
    return $Self->{Config}->{PriorityDefault};
}

sub GetAttachment {
    my ( $Self, %Param ) = @_;
    if ( !$Param{'FileID'} || !$Param{'FileID'} )
    {
        return undef;
    }
    my %Article = $Self->{TicketObject}->ArticleGet( ArticleID => $Param{ArticleID} );
    if ( !$Article{TicketID} ) {
        return { 'error' => 'Unable to find ticket' };
    }
    my $Access = $Self->{TicketObject}->CustomerPermission(
        Type     => 'ro',
        TicketID => $Article{TicketID},
        UserID   => $Param{CustomerUserID}
    );
    if ( !$Access ) {
        return { 'error' => 'Access denied' };
    }
    my %Data = $Self->{TicketObject}->ArticleAttachment(
        ArticleID => $Param{ArticleID},
        FileID    => $Param{FileID},
    );
    if ( !%Data ) {
        return { 'error' => 'Attachment not found' };
    }
    return { %Data };
}

1;
