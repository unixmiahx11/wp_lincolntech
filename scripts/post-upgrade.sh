#!/bin/bash

# change the directory to the parent folder
BASEDIR=`dirname $0`
cd $BASEDIR
cd ..

# set the permissions
sh scripts/permissions.sh
