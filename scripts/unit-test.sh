#!/usr/bin/env bash

PROJECT_PATH="$(pwd)"

echo "currently in $PROJECT_PATH"

mkdir -p "$PROJECT_PATH/magento/app/code/Madit"
mv Madit/* magento/app/code/Madit/

cd "$PROJECT_PATH/magento"


if [ -n "$INPUT_UNIT_TEST_SUBSET_PATH" ]
then
  ./vendor/bin/phpunit -c $INPUT_UNIT_TEST_CONFIG "$INPUT_UNIT_TEST_SUBSET_PATH"
else
  ./vendor/bin/phpunit -c $INPUT_UNIT_TEST_CONFIG
fi
