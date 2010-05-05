#!/bin/zsh

vim "$1" -c ":set expandtab" -c ":set tabstop=2" -c ":retab" -c ":wq";
