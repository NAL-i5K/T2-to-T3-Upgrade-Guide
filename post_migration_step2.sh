#!/usr/bin/env bash

# Further automate some things to make content display nicely.
# This should be run __after__ the primary steps from tripal_manage_analysis instructions

#  Objectives of this script:
#  - Generate panes for specific bundles (for example: create a "Other Information" pane on "Organism" bundle pages)
#  - Populate newly generated panes with required fields or further organize fields into panes
#  - Set the appropriate permissions for anonymous users to view the bundle pages (Tripal V3) - just in case

####################################################################################################

###
### Panes
###

### Generation

# Organism Page
# Get organism bundle name
org_b_name=$(drush sql-query "select name from tripal_bundle where label = 'Organism'")

# Panes to create for organism: "Other information"
tripal_ds_create_field($field_label, $field_name, $bundle_name)


# Populate

####################################################################################################

###
### Permissions (Just in case)
###

# Using https://www.drupal.org/node/802272 as a guide
# We want to make all content types at least viewable by all users (anonymous and above)










