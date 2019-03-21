<?php
//if we made this into a module, we would use the tripal jobs... but we dont so its commented out for now.
//
//global $user;
//tripal_add_job(
//  "Convert fields to properties", 'no_module',
//  'snippet_tripal_job_callback',
//  'chado_update_existing_node_urls',
//  [],
//  $user->uid
//);
//
//function snippet_tripal_job_callback(){
//
//  $obj = new ConvertFieldsToChadoProps();
//
//  $obj->convert_all();
//}
$obj = new ConvertFieldsToChadoProps();
$obj->convert_all();
class ConvertFieldsToChadoProps {
  public function convert_all() {
    $contig_n50_term = chado_insert_cvterm([
      'id' => 'local:contig_n50',
      'name' => 'contig n50',
      'definition' => 'The size of the n50 contig.',
      'cv_name' => 'local',
    ])->cvterm_id;
    $gc_term = chado_insert_cvterm([
      'id' => 'local:gc_content',
      'name' => 'GC Content',
      'definition' => '% GC',
      'cv_name' => 'local',
    ])->cvterm_id;
    $community_contact_term = chado_insert_cvterm([
      'id' => 'local:community_contact',
      'name' => 'Community Contact',
      'definition' => '',
      'cv_name' => 'local',
    ])->cvterm_id;
    $n_genes_term = chado_insert_cvterm([
      'id' => 'n_genes',
      'name' => 'Number of Genes',
      'definition' => '',
      'cv_name' => 'local',
    ])->cvterm_id;
    $scaffold_n50_term = chado_insert_cvterm([
      'id' => 'scaffold_n50',
      'name' => 'Scaffold n50',
      'definition' => '',
      'cv_name' => 'local',
    ])->cvterm_id;
    $field_photographer_term = chado_insert_cvterm([
      'id' => 'field_photographer',
      'name' => 'Field Photographer',
      'definition' => '',
      'cv_name' => 'local',
    ])->cvterm_id;
    $image_credit_term  = chado_insert_cvterm([
      'id' => 'image_credit',
      'name' => 'Image Credit',
      'definition' => '',
      'cv_name' => 'local',
    ])->cvterm_id;
    $identifying_info_term = chado_insert_cvterm([
      'id' => 'identify_info',
      'name' => 'Other Identifying Info',
      'definition' => '',
      'cv_name' => 'local',
    ])->cvterm_id;
    $sets = [
      ['field' => 'field_contig_n50', 'term' => $contig_n50_term],
      ['field' => 'field_gc_content', 'term' => $gc_term],
      ['field' => 'field_community_contact', 'term' => $community_contact_term],
      ['field' => 'field_number_of_genes', 'term' => $n_genes_term],
      ['field' => 'field_scaffold_n50', 'term' => $scaffold_n50_term],
      ['field' => 'field_photographer', 'term' => $field_photographer_term],
      ['field' => 'field_image_credit', 'term' => $image_credit_term],
      ['field' => 'field_other_identifying_informat', 'term' => $identifying_info_term],
    ];
    $source_base = 'organism';
    //$linker = 'organism_analysis';
    $linker = NULL;
    $target_base = NULL;
    $linker = NULL;
    foreach ($sets as $set) {
      $field = $set['field'];
      $cvterm_id = $set['term'];

      $field_table = 'field_data_' . $field;

      if (!db_table_exists($field_table)){

        print(t("Warning: !field  not present on this website.  Skipping.\n", ['!field' => $field]));
        continue;
      };

      print(t("Converting field: !field .\n", ['!field' => $field]));

      $this->convert_all_instances_for_field($field, $cvterm_id, $source_base, $target_base, $linker);
    }
  }
  /**
   * This function would run after a user submits a form, where they have
   * chosen a bundle, a linker table and target content type (optional), a
   * field, and a cvterm for the new field.
   *
   * @param $field The field machine name.
   */
  public function convert_all_instances_for_field($field, $cvterm_id, $source_base, $target_base = NULL, $linker = NULL) {
    $field_table = 'field_data_' . $field;
    $field_value_field = $field . '_value';
    $source_column = $source_base . '_id';
    if ($linker) {
      $target_column = $target_base . '_id';
    }
    $query = db_select($field_table, 'ft');
    $query->fields('ft', [$field_value_field, 'entity_id']);
    $query->join('chado_' . $source_base, 'c', 'c.nid = ft.entity_id');
    $query->fields('c', [$source_column]);
    $entries = $query->execute();
    foreach ($entries as $entry) {
      $value = $entry->$field_value_field;
      $record_id = $entry->$source_column;
      $target_record_id = $record_id;
      if ($linker && $target_base) {
        $targets = db_select('chado.' . $linker, 'lt')
          ->fields('lt', [$target_column])
          ->condition('lt.' . $source_column, $record_id)
          ->execute()
          ->fetchAll();
        if (count($targets) > 1) {
          print(t("Error!  More than one target for !base record: !record in !linker", [
            '!record',
            $record_id,
            !'base' => $source_base,
            '!linker',
            $linker,
          ]));
          return;
        }
        if (count($targets) == 0) {
          print(t("Error!  No targets for !base record: !record in !linker", [
            '!record',
            $record_id,
            !'base' => $source_base,
            '!linker',
            $linker,
          ]));
          return;
        }
        $target_record_id = $targets[0]->$target_column;
      }
      else {
        $target_base = $source_base;
      }
      $record = [
        'table' => $target_base,
        'id' => $target_record_id,
      ];
      $property = [
        'type_id' => $cvterm_id,
        'value' => $value,
      ];
      //allow for multiple values.
      $options = ['update_if_present' => FALSE];
      //tripal_chado API.
      $result = chado_insert_property($record, $property, $options);
      if (!$result) {
        print("error!  couldnt insert property.");
        return;
      }
      //delete the field instance?  for now we keep it.
    }
  }
}
