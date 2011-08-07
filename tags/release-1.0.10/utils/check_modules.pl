#!/usr/bin/perl
############################################################################
#
# File     : check_modules.pl
# Usage    : ./check_modules.pl
# Url      : $URL$
# Date     : $Date$
# Revision : $Revision$
# Author   : $Author$
# License  : GPL
#
# This is used to verify your system has required perl modules installed
#
############################################################################

use strict;

my @modules = (
"XML::Simple-REQUIRED for grabid.pl",
"LWP::Simple-REQUIRED for grabid.pl and grabshowsall.pl",
"Encode-REQUIRED for grabid.pl",
"DBI-OPTIONAL :: Not required for mythepisode. Used with update_programid.pl",
"DBD::mysql-OPTIONAL :: Not required for mythepisode. Used with update_programid.pl",
"DBD::AnyData-OPTIONAL :: Not required for mythepisode. Used with update_programid.pl",
);

foreach my $data (@modules) {
    my($mod,$info) = split("-",$data);
    my $status = system("perl -M$mod -e 1 > /dev/null 2>&1"); 
    if ($status) {
        print "ERROR - Module $mod is missing <> $info\n";
    } else {
        print "OK - Module $mod is installed\n";
    }
}
