#!/bin/bash
# This is step 2 of the pre-migration. It can probably be merged with the
# original pre_migration.sh script, but for testing, let's keep them separate.
# This part of the script drops some pesky indexes (particularly ones relating
# to featuregroup)
# The pesky part about these indexes involves the following error: 
# SQLSTATE[42804]: Datatype mismatch: 7 ERROR:  operator class "int4_ops" does not accept data type bigint:

drush sql-query --file=$(pwd)/scripts/generate_drop_indexes_file.sql --result-file=$(pwd)/drop_indexes_script.sql
drush sql-query --file=$(pwd)/drop_indexes_script.sql