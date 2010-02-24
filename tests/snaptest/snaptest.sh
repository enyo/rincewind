#!/bin/bash

# Portable version of resolve_symlink to work across multiple
# *nix flavors.
function resolve_symlink {
    SCRIPT=$1 NEWSCRIPT=''
    until [ "$SCRIPT" = "$NEWSCRIPT" ]; do
        if [ "${SCRIPT:0:1}" = '.' ]; then SCRIPT=$PWD/$SCRIPT; fi
        cd $(dirname $SCRIPT)
        if [ ! "${SCRIPT:0:1}" = '.' ]; then SCRIPT=$(basename $SCRIPT); fi
        SCRIPT=${NEWSCRIPT:=$SCRIPT}
        NEWSCRIPT=$(ls -l $SCRIPT | awk '{ print $NF }')
    done
    if [ ! "${SCRIPT:0:1}" = '/' ]; then SCRIPT=$PWD/$SCRIPT; fi
    echo $(dirname $SCRIPT)
}


# change to shell script real location
FPATH=$(resolve_symlink $0);

# correct pwd to resolve any symlinks and spaces in the name
OPATH=`pwd`
# OPATH=`echo $OPATH | sed s/\ /\\\\\\\\\ /`

cd $FPATH

# load options
. getoptx.sh

cd "$OPATH"

# Auto Locate PHP
PHPX=`which php`
if [[ ! -x "$PHPX" ]] ; then
    PHPX=""
    if [ -z $PHPX ] ; then
        if [ -x "/usr/local/bin/php" ] ; then
            PHPX="/usr/local/bin/php"
        fi
        if [ -x "/usr/bin/php" ] ; then
            PHPX="/usr/bin/php"
        fi
        if [ -x "/opt/local/bin/php" ] ; then
            PHPX="/opt/local/bin/php"
        fi
    fi
fi

if [[ ! -z $PHPX ]] ; then
    PHP=$PHPX
fi

# parse the options
CMD=""
while getoptex "out. php. match. help;" "$@"
do
    if [ "$OPTOPT" = "php" ] ; then
        if [ -x "$OPTARG" ] ; then
            PHP=$OPTARG
        else
            echo "The path of $OPTARG was not a valid php path."
            exit 0
        fi
    fi
    if [ "$OPTOPT" = "help" ] ; then
        CMD="$CMD --help"
    else
        if [ "$OPTOPT" != "php" ] ; then
            CMD="$CMD --$OPTOPT=$OPTARG"
        fi
    fi
done
shift $[OPTIND-1]
PTH=""
for arg in "$@"
do
    PTH="$arg"
done

# if the path begins with ./ or ../, sub in $OPATH at the front
RELPTH=`echo "$PTH" | sed "s/^\.\{1,2\}\/.*/RELPTHMATCH/"`
if [ "$RELPTH" = "RELPTHMATCH" ] ; then
    PTH="$OPATH/$PTH"
fi

PTH=`echo $PTH | sed "s/ /\\\\\\ /g"`;

# choke and die if we couldn't auto-find PHP and the user didn't supply
# a valid PHP executable path
if [ -z $PHP ] ; then
    echo "PHP was not found in any common location. You will need to"
    echo "supply the --php=<path> switch."
    exit 0
fi

# is the PHP we are using CLI or CGI
CGI=`$PHP -v | grep cgi | wc -l | sed "s/[^0-9]//g"`

# run php on the snaptest.php file with the commands
if [ "$CGI" = "0" ] ; then
    # PTH=`echo $PTH | sed "s/ /__S_P_A_C_E__/g"`
    CMD="$PHP -q $FPATH/snaptest.php --php=$PHP --nice=$NICE $CMD $PTH"
else
    # if we are running in CGI mode, we need to mangle our . characters
    # otherwise PHP mangles them in the request
    PHPSAFE=`echo "$PHP" | sed "s/\./__D_O_T__/g"`
    CMDSAFE=`echo "$CMD" | sed "s/\./__D_O_T__/g"`
    PTHSAFE=`echo "$PTH" | sed "s/\./__D_O_T__/g"`

    CMD="$PHP -q $FPATH/snaptest.php --php=$PHPSAFE $CMDSAFE $PTHSAFE"
fi

$CMD
