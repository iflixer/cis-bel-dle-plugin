<?php

$mappingData = array(
    'genres_mappings' => array(),
    'storage_mode' => $cdnhub->config['genres_storage']
);

if (isset($cdnhub->config['custom']['genres']) && is_array($cdnhub->config['custom']['genres'])) {
    foreach ($cdnhub->config['custom']['genres'] as $genre => $categoryId) {
        if (!empty($genre) && !empty($categoryId)) {
            $mappingData['genres_mappings'][$genre] = $categoryId;
        }
    }
}

echo json_encode($mappingData);