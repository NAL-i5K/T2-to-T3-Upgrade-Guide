#!/bin/bash

drush pm-enable -y ckeditor wysiwyg_filter

drush sql-query --file=scripts/generate_drop_file.sql --result-file=drop_script.sql
drush sql-query --file=drop_script.sql
drush sql-query "DROP TABLE IF EXISTS chado.all_feature_names CASCADE;"
