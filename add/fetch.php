<?php

include '../../../inc/includes.php';

$request = json_decode(file_get_contents('php://input'));

$plugin = new Plugin;

if($plugin->isActivated('estimation')) {
    Plugin::load('estimation', true);

    $facade = new PluginEstimationFacade($request);
    $facade->parseRequest();
} else {
    $response = [
        'result' => 'error',
        'comment' => 'Плагин оценки качества не установлен или не активирован'
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}

