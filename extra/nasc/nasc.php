<?php

use Composer\Autoload\ClassLoader;

/**
 * Implements hook_civicrm_config().
 */
function nasc_civicrm_config()
{
    $extRoot = dirname(__FILE__) . DIRECTORY_SEPARATOR;
    if (strpos(get_include_path(), $extRoot) === false) {
        $include_path = $extRoot . PATH_SEPARATOR . get_include_path();
        set_include_path($include_path);
    }
}

/**
 * Implements hook_civicrm_install().
 */
function nasc_civicrm_install()
{
    _nasc_register_autoloader();
}

/**
 * Implements hook_civicrm_postInstall().
 */
function nasc_civicrm_postInstall()
{
}

/**
 * Implements hook_civicrm_uninstall().
 */
function nasc_civicrm_uninstall()
{
    _nasc_register_autoloader();
}

/**
 * Implements hook_civicrm_enable().
 */
function nasc_civicrm_enable()
{
    _nasc_register_autoloader();
}

/**
 * Implements hook_civicrm_disable().
 */
function nasc_civicrm_disable()
{
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param string $op
 * @param CRM_Queue_Queue $queue
 */
function nasc_civicrm_upgrade($op, CRM_Queue_Queue $queue = null)
{

}

function _nasc_register_autoloader()
{
    global $civicrm_root;
    /** @var ClassLoader $autoloader */
    $autoloader = require $civicrm_root . '/vendor/autoload.php';
    $autoloader->addPsr4('Nasc\\', __DIR__ . '/src');
}