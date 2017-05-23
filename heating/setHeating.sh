#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd .. && pwd )"
. $DIR/heating/config.ini

case "$1" in
  "on" | "1")
      #echo 'Pali grijanje!'
      $DIR/433/433.sh 'heating 1'

      ;;
  "off" | "0")
      #echo 'Gasi grijanje!'
      $DIR/433/433.sh 'heating 0'
      ;;
  *)
      #echo 'Nisnediraj!'
      ;;
esac
