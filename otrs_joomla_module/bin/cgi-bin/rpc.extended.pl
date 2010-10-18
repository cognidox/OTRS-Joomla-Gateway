#!/usr/bin/perl
# --
# bin/cgi-bin/rpc.extended.pl - soap handle with extended object handling
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
