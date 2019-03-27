<?php

/**
* This file converst sourceuris in analysis from hardlinks to nodes to hardlinks to entities.  In addition, it places the implied relationship into chado via feature_relationship.
*/
  $sql = 'select analysis_id, sourceuri from {analysis} where sourceuri LIKE \'%node%\'';

  $results = chado_query($sql);

  $derives_from_term = chado_get_cvterm(['name' => 'derives_from', 'cv_id' => ['name' => 'relationship']]);
  if (!$derives_from_term) {
    print("Fatal error: couldn't get derives from term");
    return;
  }

  // Each analysis found is DERIVED FROM another analysis.
  foreach ($results as $result) {
    $node_string = false;

    $subject_analysis_id = $result->analysis_id;
    $node_string = $result->sourceuri;
    $original = $node_string;

    $node_string = substr($node_string, strpos($node_string, 'node/')+strlen('node/'));


    if (strpos($node_string, '/') !== FALSE) {
      $node_string = substr($node_string, 0, strpos($node_string, '/'));
    }

    $record_id = chado_get_id_from_nid('analysis', $node_string);

    if (!$record_id) {
      print "Error looking up analysis for nid " . $node_string . '.  Continuing...\n';
      continue;
    }

    $values = [
      'subject_id' => $subject_analysis_id,
      'object_id' => $record_id,
      'type_id' => $derives_from_term->cvterm_id,
    ];

    //check the record doesnt already exist

    $exists = chado_select_record('analysis_relationship', ['analysis_relationship_id'], $values);

    if (!$exists) {
      chado_insert_record('analysis_relationship', $values);
    }

    //Look up entity ID.

    $entity_id = chado_get_record_entity_by_table('analysis', $record_id);

    if (!$entity_id) {
      print ("Could not find entity for " . $record_id . ' while updating sourceuri for analysis ' . $subject_analysis_id . '.  Continuing...\n');
      continue;
    }
    // Change the URI
    $new_uri = '/bio_data/' . $entity_id;
    $match = ['analysis_id' => $subject_analysis_id];
    chado_update_record('analysis', $match, ['sourceuri' => $new_uri]);

    print("Successfully updated URI, and inserted an analysis_relationship, for analysis: " . $subject_analysis_id . '\n');

}
