#!/usr/bin/env bash

PROJECT_PATH="$(pwd)"

echo "currently in $PROJECT_PATH"
ls -alth 
ls -athl Madit
mkdir -p "$PROJECT_PATH/magento/app/code/Madit"
mv Madit/* magento/app/code/Madit/

cd "$PROJECT_PATH/magento"

ls -alth app/code/

ls -lath dev/tests/unit/



./vendor/phpunit/phpunit/phpunit "$PROJECT_PATH/magento/app/code/Madit/Atos"
./vendor/phpunit/phpunit/phpunit "$PROJECT_PATH/magento/app/code/Madit/Atos/Test/Unit/Block/DebugTest.php"

ls -lath "$PROJECT_PATH/magento/app/code/Madit/Atos/Test/Unit/Block/"


if [ -n "$INPUT_UNIT_TEST_SUBSET_PATH" ]
then
  ./vendor/bin/phpunit -c $INPUT_UNIT_TEST_CONFIG "$INPUT_UNIT_TEST_SUBSET_PATH"
else
  ./vendor/bin/phpunit -c $INPUT_UNIT_TEST_CONFIG
fi
