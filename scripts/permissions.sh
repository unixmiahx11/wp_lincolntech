#!/bin/bash

# change the directory to the parent folder
BASEDIR=`dirname $0`
cd $BASEDIR
cd ..

# set the permissions
chmod 754 -R .
chown apache:apache -R .
chmod +x scripts/*.sh
chmod 775 -R public_html/wp-content/
chmod 775 -R public_html_oh/wp-content/
