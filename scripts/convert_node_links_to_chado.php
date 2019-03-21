<?php

$orgs = db_select('chado.organism', 'o')
  ->fields('o')
  ->execute();
foreach ($orgs as $organism) {
  $organism_id = $organism->organism_id;
  $nid = chado_get_nid_from_id('organism', $organism_id);
  $node = node_load($nid);
  $links = $node->field_resource_links ?? NULL;
  if ($links) {
    $links = $links['und'];
    foreach ($links as $link) {
      $val = $link['safe_value'];
      $array = explode('|', $val);
      $key = $array[0];
      $url = $array[1];
      if (!$key) {
        tripal_report_error('organism_migration', TRIPAL_WARNING, "Error, no key found on organism !id.  String was: !val", ['!id' => $organism_id, '!val' => $val], ['print' => TRUE]);
      }
      if (strpos(strtolower($key), 'annotation') === FALSE && !strpos(strtolower($key), 'assembly') == FALSE && !strpos(strtolower($key), 'bioproject') === FALSE) {
        continue;
      }
      if (strpos(strtolower($key), 'bioproject') !== FALSE) {
        tripal_report_error('organism_migration', TRIPAL_NOTICE, "Bioproject link found for chado organism id !id: !url", ['!id' => $organism_id, '!url' => $url], ['print' => TRUE]);
      }
      if (strpos(strtolower($key), 'annotation') !== FALSE || strpos(strtolower($key), 'assembly') !== FALSE) {
        $url_array = explode('/', $url);
        $analysis_nid = $url_array[2];
        $analysis_record_id = chado_get_id_from_nid('analysis', $analysis_nid);

        if (!$analysis_record_id) {
          continue;
        }
        $vals = [
          'analysis_id' => $analysis_record_id,
          'organism_id' => $organism_id,
        ];
        $exists = chado_select_record('organism_analysis', ['organism_id'], $vals);
        if (!$exists) {
          chado_insert_record('organism_analysis', $vals);
        }
      }
    }
  }
}
