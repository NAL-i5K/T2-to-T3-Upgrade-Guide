<?php
// This script is designed to add ID values to the Tripal DS field group for newly created tables on content pages.
// Wish me luck!

// The list of manually created tables
//  all      - field_group_summary (in case the patch didn't get applied)
//  organism - the one for analysis info

// Organism
// Get the bio_data number for organisms (probably 1 but let's be safe)

    $sql = "select term_id from tripal_bundle where label='Organism'";
    $results = chado_query($sql);
    $bio_data_no = $results->fetchObject()->term_id;
    $bundle = 'bio_data_'.$bio_data_no;

// Set the table name in question

    $table_name = 'group_summary_table';

// Fetch the data from the field_group table, try to get it as a nice PHP array

    $sql = "SELECT data FROM field_group WHERE bundle = '$bundle' AND group_name = '$table_name'";
    $results = chado_query($sql);
    $data = $results->fetchObject()->data;
    $us_data = unserialize($data);
    
    // We want to add an id value (attention: lowercase 'id') to the array
    
    $us_data['format_settings']['instance_settings']['id'] = 'group_summary_table';
    print_r($us_data);
    $rs_data = serialize($us_data);
    print_r($rs_data);

    $save = db_update('field_group')
        ->fields(array(
            'data' => $rs_data
        ))
        ->condition('bundle',$bundle,'=')
        ->condition('group_name',$table_name,'=')
        ->execute();


