#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

$DIR/dbbackup.php
cp -ar $DIR/var/* $DIR/saved_var
