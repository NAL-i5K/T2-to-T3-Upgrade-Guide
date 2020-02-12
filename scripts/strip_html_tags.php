<?php
# This script removes any html tags from within the database.
# The following table/column pairs are known to have these scripts:
#   chado.organism/abbreviation
#   chado.organism/comment        Don't touch this one for now

# chado.organism/abbreviation
$sql = "update chado.organism set abbreviation=regexp_replace(abbreviation, E'<[^>]*>', '', 'gi')";
$result = chado_query($sql);
print_r($result);