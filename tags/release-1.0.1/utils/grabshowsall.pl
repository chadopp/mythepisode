#!/usr/bin/perl
############################################################################
#
# File     : grabshowsall.pl
# Usage    : ./grabshowsall.pl > shows.txt
# Date     : $Date$
# Revision : $Revision$
# Author   : $Author$
# License  : GPL
#
# Updates  :
# Chris Kapp  17-Aug-2010  Added ability to have multiple aoi's
#
# Change the $aoi(area of interest) to your country
# For single aoi my @aoi = ("US");
# For multiple aoi's my @aoi = ("US", "CA"); 
#
# Status Codes:
# 0 - Never aired
# 1 - Returning series
# 2 - Canceled/ended
# 3 - TBD/on the bubble
# 4 - In development
# 5 -
# 6 - 
# 7 - New Series
# 8 - Never aired
# 9 - Final season
# 10 - On hiatus
# 11 - Pilot ordered
# 12 - Pilot rejected
############################################################################
use LWP::Simple;
use strict;

my @aoi       = ("US");
my $current   = 0;
my $stripshow = "";
my $shortshow = "";
my $name      = "";
my $country   = "";
my $status    = "";
my @array     = ();
my @array2    = ();
my $count     = 0;

my $episodes = get "http://services.tvrage.com/feeds/show_list.php";

foreach my $episode (split("\n",$episodes) ) {
    if ( $episode =~ m#<(name)>(.*)</\1># ) {
        $name = $2;
        $name =~ s/\&amp\;/\&/g; 
        #print "Name   : $2\n";
    }
    if ( $episode =~ m#<(country)>(.*)</\1># ) {
        $country = $2;
        #print "Country: $2\n";
    }
    if ( $episode =~ m#<(status)>(.*)</\1># ) {
        $status = $2;
        if (($status == "1") || ($status == "7") || ($status == "9")) {
            $status = 1;
        }elsif (($status == "2") || ($status == "10")) {
            $status = 0;
        }
        #print "Status: $2\n";
    }
    if (grep {$_ eq $country} @aoi) {
        $stripshow = $name;
        $stripshow =~ s/The //g;
        $stripshow =~ s/\W//g;
        $stripshow = lc($stripshow);
        $name =~ s/\"//g;
        $shortshow = $name;
        $shortshow =~ s/ \($country\)//g;
        if ($shortshow =~ /\, The/) {
            $shortshow =~ s/\, The//g;
            $shortshow =~ s/$shortshow/The $shortshow/g;
        }
        if ($shortshow =~ /\, A/) {
            $shortshow =~ s/\, A//g;
            $shortshow =~ s/$shortshow/A $shortshow/g;
        }
        push(@array2, "$stripshow\t$shortshow\t$name\t$status\n");
		
        $count++;
    }
}

@array2 = sort(@array2);

foreach my $episode (@array2) {
    binmode STDOUT, ":utf8";
    print "$episode";
}

#print "Count is $count\n";
