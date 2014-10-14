#!/usr/bin/perl

use strict;
use warnings;
use utf8;
use lib $ENV{VTG_ROOT} . '/pro/Vintage/lib/pm/5';

use Getopt::Std;
use Vintage;
use Vintage::C::Util::Arrange;

my %o = ();
getopts('e:p:', \%o);

Vintage::C::Util::Arrange::run({
    project   => $o{p},
    extension => $o{e}
});

exit 0;
