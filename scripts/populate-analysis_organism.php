<?php
# This script attempts to (re)populate the analysis_organism mview. It should
#  be called PRIOR to enabling the Tripal Manage Analysis mb_output_handler

# Get the mview ID
$mview_id = chado_get_mview_id('analysis_organism');

# Call the function to populate it
if (chado_populate_mview($mview_id)) {
    echo "Successfully populated the analysis_organism mview (id " . $mview_id . ").\n";
}
else {
    echo "Failed to populate the mview";
}

