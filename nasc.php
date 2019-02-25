<?php

use Composer\Autoload\ClassLoader;
use Nasc\Setup\Step;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

const NASC_EXT_ROOT = __DIR__;

/**
 * Implements hook_civicrm_pageRun().
 *
 * @param CRM_Core_Page $page
 */
function nasc_civicrm_pageRun(&$page) {
    if ($page instanceof CRM_Contact_Page_View_Summary) {
        Civi::resources()->addScriptFile('nasc', 'js/contact_summary.js', 0);
    }
}

/**
 * @param $op
 * @param $objectName
 * @param $objectId
 * @param CRM_Core_DAO_RecurringEntity $objectRef
 */
function nasc_civicrm_post($op, $objectName, $objectId, &$objectRef) {
    $dataCopier = new \Nasc\Hook\Post\RecurringActivityCustomDataCopier();
    if ($dataCopier->applies($op, $objectName, $objectId, $objectRef)) {
        $dataCopier->apply($op, $objectName, $objectId, $objectRef);
    }
}

/**
 * @param string $formName
 * @param CRM_Core_Form $form
 */
function nasc_civicrm_preProcess($formName, &$form) {
    if ($form instanceof CRM_Contact_Form_Contact) {
        Civi::resources()->addScriptFile('nasc', 'js/contact_edit.js', 0);
    }
}

/**
 * Implements hook_civicrm_config().
 */
function nasc_civicrm_config()
{
    static $configured = false;

    if ($configured === false) {
        $extRoot = __DIR__ . DIRECTORY_SEPARATOR;
        $include_path = $extRoot . PATH_SEPARATOR . get_include_path();
        set_include_path($include_path);
        $configured = true;
    }
}

/**
 * Implements hook_civicrm_postInstall().
 */
function nasc_civicrm_postInstall()
{
    $container = get_nasc_only_container();
    $steps = nasc_get_setup_steps($container);

    /** @var Step\StepInterface $step */
    foreach ($steps as $step) {
        $step->apply();
    }
}

/**
 * Implements hook_civicrm_uninstall().
 */
function nasc_civicrm_uninstall()
{
    _nasc_register_autoloader();
    $container = get_nasc_only_container();
    $steps = nasc_get_setup_steps($container);

    /** @var Step\StepInterface $step */
    foreach ($steps as $step) {
        $step->remove();
    }
}

function nasc_get_setup_steps(ContainerInterface $container)
{
    $stepFiles = glob(NASC_EXT_ROOT . '/src/Setup/Step/*.php');
    $steps = [];
    $namespace = 'Nasc\Setup\Step\\';
    $exclude = ['StepInterface'];
    foreach ($stepFiles as $fileName) {
        $basename = substr(basename($fileName), 0, -4);
        if (!in_array($basename, $exclude)) {
            $steps[] = $container->get($namespace . $basename);
        }
    }

    return $steps;
}

/**
 * Implements hook_civicrm_container().
 *
 * @param ContainerBuilder $container
 */
function nasc_civicrm_container($container)
{
    $builder = get_nasc_container_builder();
    $container->merge($builder);
}

/**
 * @return ContainerBuilder
 */
function get_nasc_container_builder()
{
    $builder = new ContainerBuilder();
    $loader = new XmlFileLoader(
        $builder,
        new FileLocator(__DIR__ . '/config')
    );
    $loader->load('services.xml');

    return $builder;
}

/**
 * @return ContainerInterface
 */
function get_nasc_only_container()
{
    $container = get_nasc_container_builder();
    $container->compile();

    return $container;
}

function _nasc_register_autoloader()
{
    global $civicrm_root;
    /** @var ClassLoader $autoloader */
    $autoloader = require $civicrm_root . '/vendor/autoload.php';
    $autoloader->addPsr4('Nasc\\', __DIR__ . '/src');
}