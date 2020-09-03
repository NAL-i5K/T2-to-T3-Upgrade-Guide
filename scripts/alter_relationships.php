<?php
/**
 * This script will find and alter any relationships pertaining to mRNA and polypeptide
 *  where the relationship is defined as 'part_of' and changes them to 'derives_from'
 * 
 * See https://github.com/tripal/tripal/issues/1077 for more information
 */

/**
 * Template SQL:
 * update chado.feature_relationship 
 * set type_id = '42926' 
 * where feature_relationship_id in  
 * (SELECT FR.feature_relationship_id
 *       FROM chado.feature_relationship FR
 *         INNER JOIN chado.feature F on FR.subject_id = F.feature_id
 *         INNER JOIN chado.cvterm CVT on CVT.cvterm_id = F.type_id
 *         INNER JOIN chado.cvterm RCVT on RCVT.cvterm_id = FR.type_id
 *       WHERE
 *         CVT.name = 'polypeptide' and
 *         RCVT.name = 'part_of')
 */
# Get the cv_id for the Sequence Ontology
    $sql = "SELECT cv_id 
            FROM {cv} 
            WHERE name='sequence'";
    $results = chado_query($sql);
    $cv_id = $results->fetchObject()->cv_id;
    print("cv_id: " . $cv_id . "\n");

# Get the cvterm_id from chado.cvterm relating for 'part_of'
    $sql = "SELECT cvterm_id 
            FROM {cvterm}
            WHERE 
                cv_id = :cv_id AND
                name = 'derives_from'";
    $args = array(
        ':cv_id' => $cv_id,
    );
    $results = chado_query($sql, $args);
    $cvterm_id = $results->fetchObject()->cvterm_id;
    print("cvterm_id " . $cvterm_id . "\n");


# Quick test of how to use chado_query with table alias
    $type = 'polypeptide';
    $sql = "SELECT count(FR.feature_relationship_id)
        FROM {feature_relationship} FR
            INNER JOIN {feature} F on FR.subject_id = F.feature_id
            INNER JOIN {cvterm} CVT on CVT.cvterm_id = F.type_id
            INNER JOIN {cvterm} RCVT on RCVT.cvterm_id = FR.type_id
        WHERE
            CVT.name = :type";
    $args = array(
        ':type' => $type,
    );
    $results = chado_query($sql, $args);
    print("poly: ". $results->fetchObject()->count . "\n");
    // It works just fine...

# All together now
    $sql = "UPDATE {feature_relationship}
            SET type_id = :cvterm_id
            WHERE feature_relationship_id in
            (
                SELECT FR.feature_relationship
                FROM {feature_relationship} FR
                    INNER JOIN {feature} F on FR.subject_id = F.feature_id
                    INNER JOIN {cvterm} CVT on CVT.cvterm_id = F.type_id
                    INNER JOIN {cvterm} RCVT on RCVT.cvterm_id = FR.type_id
                WHERE
                    CVT.name = 'polypeptide' AND
                    RCVT.name = 'part_of')
            )
            ";
    $args = array(
        ':cvterm_id' => $cvterm_id,
    );

    $results = chado_query($sql, $args);
