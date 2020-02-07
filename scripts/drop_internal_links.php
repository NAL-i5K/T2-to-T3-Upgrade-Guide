<?php
/**
 * This script attempts to find any internal links on organism pages and drop them.
 *   It makes sure that the internal links are linking to analysis pages
 * 
 * @todo This may not be entirely necessary, these nodes perhaps should have been dropped
 * with the 4 step program at an earlier stage. This must be tested. 
 */

/**
 * Node URLs (these are already enabled on Organism pages and are therefore redundant)
 */

# Get the bundle number for organism
    $sql = "select term_id from tripal_bundle where label='Organism'";
    $results = chado_query($sql);
    $org_no = $results->fetchObject()->term_id;

# Get the list of URLs associated with organisms
    $organism_resource_links = "bio_data_".$org_no."_resource_links_url";
    $organism_resource_table = "field_data_bio_data_".$org_no."_resource_links";

    //SQL = select bio_data_#_resource_links_url from field_data_bio_data_#_resource_links where bio_data_#_resource_links_url similar to '/?node%'
    $sql = "select $organism_resource_links, entity_id, delta from $organism_resource_table where $organism_resource_links similar to '/?node%'";

    //echo $sql."\n";
    $results = chado_query($sql);

    # Cycle through the results
    $nids = array();
    foreach ($results as $result)
    {
        //array_push($nids,$result->$organism_bundle_table);
        preg_match('/([0-9])+/',$result->$organism_resource_links,$matches);
        array_push($nids,array('node_id'=>$matches[0],'entity_id'=>$result->entity_id,'delta'=>$result->delta));
    }

    // for debugging: print_r($nids);

# Grab all the nids from node table where type=chado_analysis
    $sql = "select nid from node where type='chado_analysis'";
    $results = chado_query($sql);
    $chado_analyses = array();

    foreach ($results as $result)
    {
        array_push($chado_analyses,$result->nid);
    }

# Compare the URL node to the chado_analysis nodes
    # $nids = small array of node IDs found in URLs
    # $chado_analyses = possibly big array of all nids associated with chado_analysis

    $total_matches = 0;
    $sql = "";

    # Better to go through big array few number of times
    foreach ($nids as $nid)
    {
        $matches_found = 0;
        foreach($chado_analyses as $chado_analysis)
        {
            if ($nid['node_id'] == $chado_analysis)
            {
                $matches_found += 1;
                $total_matches += 1;
                // This is where we would do the sql query if we wanted to be uncouth
                // We really should just create a nice SQl query that will do all this work for us
                //   using regex_replace and 'similar to' -- for starters: 
                //   select bio_data_1_resource_links_url from field_data_bio_data_1_resource_links where bio_data_1_resource_links_url similar to '/?node%';
                //   Otherwise, we will generate many many individual update queries, as seen below
                //   (hopefully commented out and kept around to show how NOT to do things)
                // In the meantime:
                //   basic query: update field_data_bio_data_1_resource_links set deleted=1 where entity_id=56231 and delta=5;
                // old and bad way: $sql .= "update ".$organism_bundle_table ." set deleted=1 where entity_id=56231 and delta=5;\n";
                $sql = "update ".$organism_resource_table." set deleted=1 where entity_id = ".$nid['entity_id']." and delta = ".$nid['delta'];
                chado_query($sql);
            }
        }
        if ($matches_found)
        {
            //echo $nid . ": ".$matches_found." matches found.\n";
        }
    }

    echo "Deleted all '/node/' URLs that linked to chado_analysis pages\n";

/**
 * Annotation URLs (these originally pointed to lists of annotations in the T2 days, but are now
 * deprecated).
 */

# Get all the organism links that are like /annotation/*
    # This one is simpler because we're just disabling ALL the /annotations/ links, not just specific ones
    #reminder: table name  = field_data_bio_data_1_resource_links   = $organism_resource_links
    #reminder: column name = bio_data_1_resource_links_url          = $field_data_bio_data_1_resource_links

    $sql = "update $organism_resource_table set deleted=1 where $organism_resource_links similar to '/?annotation%'";
    chado_query($sql);

    echo "Deleted all '/annotation/' URLs\n";

// Dropped all the internal links. Do we advise the user to clear the cache or should we do that for them?
echo "URLs set as deleted, please remember to clear the drupal cache\n";

