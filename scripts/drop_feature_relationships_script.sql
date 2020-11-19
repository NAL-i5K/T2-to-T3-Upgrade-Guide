-- Delete feature_relationship records with no matching features for subject_id
DELETE FROM chado.feature_relationship
WHERE subject_id in (
    SELECT subject_id
    FROM chado.feature_relationship FR LEFT JOIN chado.feature F ON FR.subject_id = F.feature_id
    WHERE F.feature_id IS NULL
);

-- Delete feature_relationship records with no matching features for object_id
DELETE FROM chado.feature_relationship
WHERE object_id in (
    SELECT object_id
    FROM chado.feature_relationship FR LEFT JOIN chado.feature F ON FR.object_id = F.feature_id
    WHERE F.feature_id IS NULL
);