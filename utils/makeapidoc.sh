#!/bin/sh

pwd=`pwd`
pwd=`basename $pwd`
if [ "$pwd" != "cartograph2" ]; then echo "Run this from the cartograph2 directory to regenerate the API docs."; exit; fi

# delete the old documentation
svn remove -q --force docs/apidoc

# generate it
phpdoc -o HTML:frames:earthli -t docs/apidoc \
   -d docs/coder,classes,dispatchers \
   -f index.php \
   -f lib/library.php,lib/functions.php

# add it back to the version controller
svn add -q docs/apidoc
svn add -q docs/apidoc/*
svn add -q docs/apidoc/*/*
svn add -q docs/apidoc/*/*/*
