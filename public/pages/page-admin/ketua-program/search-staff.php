<?php

require_once __DIR__ . '/../../../controllers/KetuaProgramController.php';

if (isset($_GET["term"])) {
    $term = $_GET["term"];

    $controller = new KetuaProgramController();
    $response = $controller->searchStaf($term); 

    include __DIR__ . '/search-result-table.php';
}