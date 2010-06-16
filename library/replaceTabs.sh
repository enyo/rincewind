#!/bin/bash

find . -iname '*.php' -exec perl -i -p0e "s/\?>\s*\z//" '{}' \;
find . -iname '*.php' -exec vim -c ":set expandtab" -c ":set tabstop=2" -c ":retab" -c ":wq" '{}' \;

