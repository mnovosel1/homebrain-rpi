 #!/bin/bash
 
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd .. && pwd )"

case "$1" in
    "sock1_on" | "heating on" | "heating 1")
        sudo $DIR/433/codesend 7721996 2>/dev/null
        ;;
    "sock1_off" | "heating off" | "heating 0")
        sudo $DIR/433/codesend 7721987 2>/dev/null
        ;;
    "sock2_on")
        sudo $DIR/433/codesend 7697420 2>/dev/null
        ;;
    "sock2_off")
        sudo $DIR/433/codesend 7697411 2>/dev/null
        ;;
    "sock3_on")
        sudo $DIR/433/codesend 7691276 2>/dev/null
        ;;
    "sock3_off")
        sudo $DIR/433/codesend 7691267 2>/dev/null
        ;;
esac