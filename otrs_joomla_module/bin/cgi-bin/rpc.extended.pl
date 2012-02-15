#!/usr/bin/perl
# --
# bin/cgi-bin/rpc.extended.pl - soap handle with extended object handling
# Copyright (c) 2010 Cognidox Ltd
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
# --

use strict;
use warnings;

# use ../../ as lib location
use FindBin qw($Bin);
use lib "$Bin/../..";
use lib "$Bin/../../Kernel/cpan-lib";

use SOAP::Transport::HTTP;
use Kernel::Config;
use Kernel::System::Encode;
use Kernel::System::Log;
use Kernel::System::DB;
use Kernel::System::PID;
use Kernel::System::Main;
use Kernel::System::Time;
use Kernel::System::User;
use Kernel::System::Group;
use Kernel::System::Queue;
use Kernel::System::CustomerUser;
use Kernel::System::Ticket;
use Kernel::System::LinkObject;

use vars qw($VERSION);
$VERSION = qw($Revision: 1.14 $) [1];

SOAP::Transport::HTTP::CGI->dispatch_to('Core')->handle;

package Core;

sub new {
    my $Self = shift;

    my $Class = ref($Self) || $Self;
    bless {} => $Class;

    return $Self;
}

sub Dispatch {
    my ( $Self, $User, $Pw, $Object, $Method, %Param ) = @_;

    $User ||= '';
    $Pw   ||= '';

    # common objects
    my %CommonObject = ();
    $CommonObject{ConfigObject} = Kernel::Config->new();
    $CommonObject{EncodeObject} = Kernel::System::Encode->new(%CommonObject);
    $CommonObject{LogObject}    = Kernel::System::Log->new(
        LogPrefix => 'OTRS-EXT-RPC',
        %CommonObject,
    );
    $CommonObject{MainObject}         = Kernel::System::Main->new(%CommonObject);
    $CommonObject{DBObject}           = Kernel::System::DB->new(%CommonObject);
    $CommonObject{PIDObject}          = Kernel::System::PID->new(%CommonObject);
    $CommonObject{TimeObject}         = Kernel::System::Time->new(%CommonObject);
    $CommonObject{UserObject}         = Kernel::System::User->new(%CommonObject);
    $CommonObject{GroupObject}        = Kernel::System::Group->new(%CommonObject);
    $CommonObject{QueueObject}        = Kernel::System::Queue->new(%CommonObject);
    $CommonObject{CustomerUserObject} = Kernel::System::CustomerUser->new(%CommonObject);
    $CommonObject{TicketObject}       = Kernel::System::Ticket->new(%CommonObject);
    $CommonObject{LinkObject}         = Kernel::System::LinkObject->new(%CommonObject);

    # Set up an empty web request - all the data we need has come in
    # from SOAP, so extra query data should be ignored. If the query
    # isn't blanked, there can be problems under Windows with 'new CGI'
    # hanging
    $CommonObject{WebRequest} = {};

    # Load RPC extra objects, which can be defined by modules
    my $rpcExtras = $CommonObject{ConfigObject}->Get('SOAP::ModuleObject');
    if ($rpcExtras && ref($rpcExtras) eq 'HASH') {
        while (my ($key, $hash) = each %$rpcExtras) {
            next unless (ref($hash) eq 'HASH');
            while (my ($obj, $mod) = each %$hash) {
                next if (exists $CommonObject{$obj});
                eval ("use $mod;");
                if (!$@) {
                    $CommonObject{$obj} = ${mod}->new(%CommonObject);
                }
            }
        }
    }

    my $RequiredUser     = $CommonObject{ConfigObject}->Get('SOAP::User');
    my $RequiredPassword = $CommonObject{ConfigObject}->Get('SOAP::Password');

    if (
        !defined $RequiredUser
        || !length $RequiredUser
        || !defined $RequiredPassword || !length $RequiredPassword
        )
    {
        $CommonObject{LogObject}->Log(
            Priority => 'notice',
            Message  => "SOAP::User or SOAP::Password is empty, SOAP access denied!",
        );
        return;
    }

    if ( $User ne $RequiredUser || $Pw ne $RequiredPassword ) {
        $CommonObject{LogObject}->Log(
            Priority => 'notice',
            Message  => "Auth for user $User (pw $Pw) failed!",
        );
        return;
    }

    if ( !$CommonObject{$Object} ) {
        $CommonObject{LogObject}->Log(
            Priority => 'error',
            Message  => "No such Object $Object!",
        );
        return "No such Object $Object!";
    }

    return $CommonObject{$Object}->$Method(%Param);
}

1;
