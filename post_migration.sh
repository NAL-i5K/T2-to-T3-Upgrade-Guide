# Go to your site's `/sites/all/modules/custom` folder first!
git clone https://github.com/statonlab/tripal_manage_analyses.git
drush pm-enable tripal_manage_analyses

drush scr scripts/migrate_node_fields.php
drush scr scripts/convert_node_links_to_chado.php
drush scr scripts/convert_sourceuris_from_nodes.php
