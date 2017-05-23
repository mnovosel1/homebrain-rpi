#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd .. && pwd )"
. $DIR/lan/config.ini

for h in ${HOST[*]}; do
  echo "$h"
done
