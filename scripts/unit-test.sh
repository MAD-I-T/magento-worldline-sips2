#!/usr/bin/env bash

PROJECT_PATH="$(pwd)"

echo "currently in $PROJECT_PATH"
ls -alth 
ls -athl Madit

cp -r Madit magento/app/code/

cd "$PROJECT_PATH/magento"

ls -alth app/code/


if [ -n "$INPUT_UNIT_TEST_SUBSET_PATH" ]
then
  ./vendor/bin/phpunit -c $INPUT_UNIT_TEST_CONFIG "$INPUT_UNIT_TEST_SUBSET_PATH"
else
  ./vendor/bin/phpunit -c $INPUT_UNIT_TEST_CONFIG
fi
