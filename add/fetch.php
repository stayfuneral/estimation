<?php

include '../../../inc/includes.php';

$request = json_decode(file_get_contents('php://input'));

Plugin::load('estimation', true);

$facade = new PluginEstimationFacade($request);
$facade->parseRequest();