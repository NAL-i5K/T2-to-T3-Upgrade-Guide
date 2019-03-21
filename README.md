## Upgrade Suite

The goal of this repository is to provide a streamlined custom migration path for the i5k website from Tripal 2 to Tripal 3.  It may serve as a good starting point for other sites, but you will likely have to customize it for your own needs.  Please don't follow this guide blindly!


# Upgrade Process

### Prior to migrating

* [Upgrading Chado](Upgrading_chado_1.2_to_1.3.md)




### The migration itself

Follow [the Tripal 2 to 3 Migration guide](https://tripal.readthedocs.io/en/latest/user_guide/install_tripal/upgrade_from_tripal2.html).

### After migrating

* Install and enable [Tripal Manage Analyses](https://github.com/statonlab/tripal_manage_analyses).
  - This module will provide new chado tables for you, namely an `organism_analsyis` linker table.
  - This module also includes gene fields, and fields to link organisms to analyses and vice versa.
* Install and enable [Tripal HQ](https://github.com/statonlab/tripal_hq)
  - This module Lets users submit Chado content in your site in a safe way.
* Install and enable [field permissions](https://www.drupal.org/project/field_permissions)
  - Use in combination with Tripal HQ to hide confusing fields (dbxref for example) from users.
  - See [this guide on how to use and configure](https://tripal.readthedocs.io/en/latest/user_guide/content_types/field_permissions.html?).
* Convert Drupal fields not handled by the Tripal migration to chado properties.
  - Done in `migrate_node_fields.php`.
* Convert node-based internal-links to "future proof" Chado-based links.
  - Done in `convert_node_links_to_chado.php`.
  - Messages printed are to provide an inventory of non-analysis links.  You'll have the link and the Chado organism_id to add the link via Chado later if you desire.




#### Configuring fields

The Tripal user's guide has [detailed instructions on how to configure fields](https://tripal.readthedocs.io/en/latest/user_guide/content_types/configuring_page_display.html#rearranging-fields).


Originally, I had exported my field configurations into deployable modules using the [Drupal features module](https://www.drupal.org/project/features).  However, when Tripal creates field groupings, it gives it a name with random numbers in the string.  This means that after you upgrade from Tripal 2 to Tripal 3 on your site, your field group names will be **incompatible** with mine.  You can still use Drupal features, but the starting feature must be exported from a copy of your database, or, you must delete your feature groups prior to enabling the feature module.
