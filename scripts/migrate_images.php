<?php
/**
 * This script is designed to migrate any organism images used in the Tripal 2 nodes (custom field) to Tripal 3 Entities.
 * This is a two step process that is designed to run AFTER the T2-T3 migration (via Tripal UI) has completed:
 * 
 *  1. Associate the existing files in the file_managed table to the Organism entities in the database
 *  2. Move the files from the old custom location to the standard location. (see note below)
 * 
 * NOTE: It appears that all the images that were uploaded to the custom location were ALSO
 *       loaded into the standard public:// location. 
 *       This makes step 2 not necessary.
 */


 /**
  * Step 1: Associate existing images with Tripal 3 Entities.
  *
  * We need several pieces of information in order to manually insert/associate the images:
  *     1. Entity id of the organism (from chado_bio_data_# associated with organisms)
  *     2. Image fid (from file_managed)
  *     3. Image dimensions
  *     4. Scientific name of organism (for 508 compliance via alt tag)
  * Armed with this information, we will insert into the field_data_data__image table:
  *
  *             entity_type, bundle, deleted, entity_id, revision_id,
  *             language, delta, data__image_fid, data__image_alt, 
  *             data__image_title, data__image_width, data__image_height
  */
  # Get the bio_data_# associated with organisms on this site (it's not always #1)
    $sql = "select term_id from tripal_bundle where label='Organism'";
    $results = chado_query($sql);
    $org_no = $results->fetchObject()->term_id;
      echo "Organism has bundle ID of $org_no on this server\n\n";
    
  # Get the list of files associated with current organism nodes.
    $sql = "select  fm.uri,
    cbd.entity_id,
    fm.fid,
    n.title
    from file_managed fm 
        join field_data_field_organism_image field
        on fm.fid=field.field_organism_image_fid
        join node n
        on n.nid=field.entity_id
        join chado_bio_data_$org_no cbd
        on cbd.nid=n.nid";
    $results = chado_query($sql);

    // Build an array with a list of files and associated details
    $organism_details = array();
    foreach ($results as $result)
    {
      // Columns in $result: uri, entity_id, fid, title
      $image_file = file_stream_wrapper_get_instance_by_uri($result->uri);
      $image_file_path = $image_file->realpath();
      //echo $image_file_path."\n";

      // Get image dimensions
      // todo - check to see if the file exists and if it is an image
      //        DO NOT push to the database if not
      $imagesize = getimagesize($image_file_path);

      // Once we've gathered all the items, push into an array and then
      //   push into the database (field_data_data__image)
      array_push($organism_details,array(
        'entity_type'=>'TripalEntity',
        'bundle'=>t('bio_data_'.$org_no),
        'deleted'=>0,
        'entity_id'=>$result->entity_id,
        'revision_id'=>$result->entity_id,
        'language'=>'und',
        'delta'=>0,
        'data__image_fid'=>$result->fid,
        'data__image_alt'=>$result->title,
        'data__image_title'=>$result->title,
        'data__image_width'=>$imagesize[0],
        'data__image_height'=>$imagesize[1]
      ));
    }

    // This is where we load it all into the database (field_data_data__image)
    foreach($organism_details as $org_d)
    {
      $insert = db_insert('field_data_data__image')
                ->fields($org_d)
                ->execute();
    }

    echo $insert;
    
    
/**
  * Step 2: Move the files from the old custom location to the standard location..
  *
  * This is probably not necessary. Check this.
  */