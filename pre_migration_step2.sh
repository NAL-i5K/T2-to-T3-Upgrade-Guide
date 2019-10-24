#!/bin/bash
# This is step 2 of the pre-migration. It can probably be merged with the
# original pre_migration.sh script, but for testing, let's keep them separate.
# This part of the script drops some pesky indexes (particularly ones relating
# to featuregroup)

drush sql-query --file=$(pwd)/scripts/drop_indexes.sql