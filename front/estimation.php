<?php

require __DIR__ .'/../../../inc/includes.php';

Html::header('Оценка качества', $_SERVER['PHP_SELF']);

Search::show(PluginEstimationEstimation::class);

Html::footer();