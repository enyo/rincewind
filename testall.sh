#/bin/bash

chmod 400 ./tests/library/Logger/unwritableFile.log

./tests/snaptest/snaptest.sh --verbose ./tests/library/$1/

