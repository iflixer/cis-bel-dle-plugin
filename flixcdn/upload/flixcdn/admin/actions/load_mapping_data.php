<?php

$mappingData = array(
    'genres_mappings' => array(),
    'storage_mode' => $flixcdn->config['genres_storage']
);

if (isset($flixcdn->config['custom']['genres']) && is_array($flixcdn->config['custom']['genres'])) {
    foreach ($flixcdn->config['custom']['genres'] as $genre => $categoryId) {
        if (!empty($genre) && !empty($categoryId)) {
            $mappingData['genres_mappings'][$genre] = $categoryId;
        }
    }
}

echo json_encode($mappingData);