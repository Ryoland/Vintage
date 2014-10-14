#!/usr/bin/perl

use strict;
use warnings;
use utf8;
use lib $ENV{VTG_ROOT} . '/pro/Vintage/lib/pm/5';

use Vintage;
use Vintage::C::Util::Env;

Vintage::C::Util::Env::configure();

exit 0;
