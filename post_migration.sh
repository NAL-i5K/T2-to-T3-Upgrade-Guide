#!/usr/bin/env bash

# Populate the analysis_organism table (prior to tripal_manage_analysis)
drush scr scripts/populate-analysis_organism.php

# Go to your site's `/sites/all/modules/custom` folder first!
git clone https://github.com/statonlab/tripal_manage_analyses.git
drush pm-enable tripal_manage_analyses

drush scr scripts/migrate_node_fields.php
drush scr scripts/convert_node_links_to_chado.php
drush scr scripts/convert_sourceuris_from_nodes.php

# Get the Tripal HQ and Tripal EUtils modules, move them to the parent directory, then enable them.
git clone https://github.com/NAL-i5K/tripal_eutils.git
git clone https://github.com/statonlab/tripal_hq.git
# Also grab the field_permissions module which is recommended for use with HQ.
drush pm-download field_permissions

mv tripal_eutils ..
mv tripal_hq ..

drush pm-enable tripal_eutils -y
drush pm-enable tripal_hq tripal_hq_imports tripal_hq_permissions -y
# Also field_permissions
drush pm-enable field_permissions -y
