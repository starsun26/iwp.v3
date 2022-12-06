<?php

define('WPGRABBER_CORE_VERSION', '3.0.3');

define('WPGRABBER_PLUGIN_INSTALL_DIR', WPGRABBER_PLUGIN_DIR . 'install' . DIRECTORY_SEPARATOR);
define('WPGRABBER_PLUGIN_CORE_DIR', WPGRABBER_PLUGIN_DIR . 'core' . DIRECTORY_SEPARATOR);
define('WPGRABBER_PLUGIN_TPL_DIR', WPGRABBER_PLUGIN_DIR . 'tmpl' . DIRECTORY_SEPARATOR);

if (!session_id()) {
    session_start();
}

function wpgIsDemo()
{
    return ($_SERVER['HTTP_HOST'] == 'wpgrabber-tune.blogspot.com');
}

function wpgIsDebug()
{
    return is_file(WPGRABBER_PLUGIN_DIR . 'debug');
}

if (wpgIsDebug()) {
    ini_set('display_errors', true);
    error_reporting(E_ALL ^ E_NOTICE);
}

function wpgIsPro()
{
    if (defined('WPGRABBER_VERSION')) {
        $v = explode(' ', WPGRABBER_VERSION);
        return (isset($v[1]) and $v[1] == 'Professional');
    }
    return false;
}

function wpgIsStandard()
{
    if (wpgIsPro()) {
        return true;
    }
    if (defined('WPGRABBER_VERSION')) {
        $v = explode(' ', WPGRABBER_VERSION);
        return (isset($v[1]) and $v[1] == 'Standard');
    }
    return false;
}

function wpgIsLite()
{
    if (wpgIsStandard()) {
        return true;
    }
    if (defined('WPGRABBER_VERSION')) {
        $v = explode(' ', WPGRABBER_VERSION);
        return (isset($v[1]) and $v[1] == 'Lite');
    }
    return false;
}

function wpgPlugin()
{
    if (wpgIsPro()) {
        return 'WPGPluginPro';
    } elseif (wpgIsStandard()) {
        return 'WPGPluginStandard';
    } elseif (wpgIsLite()) {
        return 'WPGPluginLite';
    } else {
        return 'WPGPlugin';
    }
}

require_once(WPGRABBER_PLUGIN_CORE_DIR . 'WPGPlugin.php');
require_once(WPGRABBER_PLUGIN_CORE_DIR . 'WPGErrorHandler.php');
require_once(WPGRABBER_PLUGIN_CORE_DIR . 'WPGHelper.php');
require_once(WPGRABBER_PLUGIN_CORE_DIR . 'WPGTable.php');
require_once(WPGRABBER_PLUGIN_CORE_DIR . 'WPGTools.php');
require_once(WPGRABBER_PLUGIN_CORE_DIR . 'WPGWordPressDB.php');
require_once(WPGRABBER_PLUGIN_CORE_DIR . 'TGrabberCore.php');
require_once(WPGRABBER_PLUGIN_CORE_DIR . 'TGrabberWordPress.php');
require_once(WPGRABBER_PLUGIN_CORE_DIR . 'TGrabberWPOptions.php');








if (!class_exists('simple_html_dom_node'))
{

    if (version_compare(PHP_VERSION, '7.3.0') >= 0) {
        require_once(WPGRABBER_PLUGIN_CORE_DIR . 'simple_html_dom__1.9.1.php');
    }else{
        require_once(WPGRABBER_PLUGIN_CORE_DIR . 'simple_html_dom.php');
    }


}

call_user_func(array(wpgPlugin(), 'load'));
?>