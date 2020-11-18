<?php
/*
 -------------------------------------------------------------------------
 Estimation plugin for GLPI
 Copyright (C) 2020 by the Estimation Development Team.

 https://github.com/pluginsGLPI/estimation
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Estimation.

 Estimation is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Estimation is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Estimation. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('PLUGIN_ESTIMATION_VERSION', '1.0');

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_estimation() {
   global $PLUGIN_HOOKS;

   $classes = [
       PluginEstimationEstimation::class,
       PluginEstimationConfigs::class,
       PluginEstimationCron::class,
       PluginEstimationTicket::class,
       PluginEstimationFacade::class,
       PluginEstimationMenu::class
   ];

    foreach ($classes as $class) {
        Plugin::registerClass($class);
   }

   $PLUGIN_HOOKS['csrf_compliant']['estimation'] = true;
   $PLUGIN_HOOKS['config_page']['estimation'] = 'front/configs.php';
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_estimation() {
   return [
      'name'           => 'Оценка качества работы с заявкой',
      'version'        => PLUGIN_ESTIMATION_VERSION,
      'author'         => 'Roman Gonyukov',
      'license'        => '',
      'homepage'       => '',
      'requirements'   => [
         'glpi' => [
            'min' => '9.2',
         ]
      ]
   ];
}

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_estimation_check_prerequisites() {

   //Version check is not done by core in GLPI < 9.2 but has to be delegated to core in GLPI >= 9.2.
   $version = preg_replace('/^((\d+\.?)+).*$/', '$1', GLPI_VERSION);
   if (version_compare($version, '9.2', '<')) {
      echo "This plugin requires GLPI >= 9.2";
      return false;
   }
   return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_estimation_check_config($verbose = false) {
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      echo __('Installed / not configured', 'estimation');
   }
   return false;
}
