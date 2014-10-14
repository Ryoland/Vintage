#!/bin/bash

##########
## Desc ##
##########

##
 # Package
 ##

##########
## Init ##
##########

ARGUMENTS=e:p:;

HELP() {
    echo "Usage: $0 [Argument(s)]";
    echo "";
    echo "Optional Argument(s):";
    echo "    -e File extension.";
    echo "    -p Project name.";
}

source $VTG_ROOT/pro/Vintage/lib/sh/initialize;

##########
## Conf ##
##########

##########
## Func ##
##########

proc() {

    OPTIONS="";

    if [ ! -z $ARG_e ]; then OPTIONS="$OPTIONS -e $ARG_e"; fi;
    if [ ! -z $ARG_p ]; then OPTIONS="$OPTIONS -p $ARG_p"; fi;

    $0.i.pl $OPTIONS;
}

##########
## Proc ##
##########

proc;

##########
## Exit ##
##########

exit 0;
