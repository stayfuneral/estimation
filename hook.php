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

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_estimation_install() {

    require __DIR__ . '/inc/Setup.php';
    $setup = Estimation\Setup::getInstance();

    $setup->installDbTables();
    $setup->saveDefaultConfigs();
    $setup->registerCronTask();

    return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_estimation_uninstall() {

    require __DIR__ . '/inc/Setup.php';
    $setup = Estimation\Setup::getInstance();

    $setup->uninstallDbTables();
    $setup->unRegisterCronTask();

    return true;
}
