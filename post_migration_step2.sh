#!/usr/bin/env bash

# Further automate some things to make content display nicely.
# This should be run __after__ the primary steps from tripal_manage_analysis instructions

#  Objectives of this script:
#  - Set the appropriate permissions for anonymous users to view the bundle pages (Tripal V3) - just in case
#  - Remove the Internal links from the "Links" section (external links will be handled later)
#    - /annotations/xxx are being deprecated? (pages that list annotations associated to the organism)
#    - /node/xxx point to analysis that are linked via the tripal_manage_analysis module
#

####################################################################################################


###
### Permissions (Just in case)
###

# Using https://www.drupal.org/node/802272 as a guide
# We want to make all content types at least viewable by all users (anonymous and above)
# Look for pages that allow comments, disable 

###
### Organism links
###
# @todo automate cache clearing (get drupal_root, drush --root=/path/of/drupal_root cc all)
# This portion of the script attempts to remove "internal" links from the Links section on Organism
#   pages. It does this by setting deleted = 1 in the database on any entry in 
#   field_data_bio_data_#_resource_links where the bio_data_#_resource_links_url is a /node/% url and 
#   that the % is a chado_analysis node type. (Care must be taken to make sure that the integer for the
#   bio_data type for organism is substituted here for "1" on sites where this might not be the case).

drush scr scripts/drop_internal_links.php


###
### Organism images
###
# Call the migrate_images.php script. Details are in that file

drush scr scripts/migrate_images.php

###
### Strip HTML formatting
###
# Call the strip_html_tags.php script. Details are in that file.

drush scr scripts/strip_html_tags.php

###
### Feature relationship dropping
###
### We need to drop a number of feature_relationship records because
### their associated features had been deleted without consideration
### to foreign key constraints (circa Tripal 2).
# Call the drop script directly. No need to generate it.

drush sql-query --file=$(pwd)/scripts/drop_feature_relationships_script.sql

###
### Add Chado 1.3 constraints back in
###
### The standard Chado conversion that Tripal performs leaves out a
### number of important table constraints (primary keys) that are
### necessary for the database to remain in a consistent state (such 
### as when a feature is deleted and its corresponding feature props
### need to be deleted as well)

drush sql-query --file=$(pwd)/scripts/add_constraints_1.3.sql