<?php
/**
 * Plugin Name: SLiMS Upgrader
 * Plugin URI: -
 * Description: -
 * Version: 1.0.0
 * Author: Drajat Hasan
 * Author URI: https://t.me/drajathasan
 */

// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

require_once __DIR__ . '/vendor/autoload.php';

// registering menus or hook
$plugin->registerMenu("system", 'Periksa Pembaharuan', __DIR__ . '/pages/check_update.php');