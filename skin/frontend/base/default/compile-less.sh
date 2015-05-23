#!/bin/sh

for file in less/*.less; do lessc -f $file -o css/`basename $file .less`.css ; done
