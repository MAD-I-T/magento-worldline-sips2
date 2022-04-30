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


echo "$INPUT_UNIT_TEST_SUBSET_PATH"
    ./vendor/bin/phpunit "$PROJECT_PATH/magento/app/code/Madit/Atos/Test/Unit"

echo "$INPUT_UNIT_TEST_CONFIG"
  ./vendor/bin/phpunit "$PROJECT_PATH/magento/app/code/Madit"



cat "$INPUT_UNIT_TEST_CONFIG"

if [ -n "$INPUT_UNIT_TEST_SUBSET_PATH" ]
then
  ./vendor/bin/phpunit -c $INPUT_UNIT_TEST_CONFIG "$INPUT_UNIT_TEST_SUBSET_PATH"
else
  ./vendor/bin/phpunit -c $INPUT_UNIT_TEST_CONFIG
fi
