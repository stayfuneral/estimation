<?php

require __DIR__ .'/../../../inc/includes.php';
require __DIR__ .'/../../../kint.phar';

Html::header('Оценка качества', $_SERVER['PHP_SELF']);

Search::show(PluginEstimationEstimation::class);

Html::footer();