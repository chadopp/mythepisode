#!/usr/bin/perl
############################################################################
#
# File     : grabshows.pl
# Usage    : ./grabshows.pl
# Revision : 1.0
# Author   : C. Oppliger
# License  : GPL
#
############################################################################
use LWP::Simple;
use strict;

my $current = 0;
my $stripshow = "";
my $shortshow = "";
my @array = ();

my $site = get "http://epguides.com/common/allshows.txt";

foreach my $line ( split("\n", $site) ) {
    chomp $line;  
    next if $line !~ /^\"/;
    my($show,$onair,$offair,$numeps,$eplength,$network,$country) = split("\,",$line);
    next if $country !~ "US";
    if ($offair =~ /^_/) {
        $current = 1;
    }else{
        $current = 0;
    }
    $stripshow = $show;
    $stripshow =~ s/The //g;
    $stripshow =~ s/\W//g;
    $stripshow = lc($stripshow);
    $show =~ s/\"//g;
    $shortshow = $show;
    $shortshow =~ s/ \(US\)//g;
    push(@array, "$stripshow\t$shortshow\t$show\t$current\n");
}

@array = sort(@array);

foreach my $episode (@array) {
    print "$episode";
}
