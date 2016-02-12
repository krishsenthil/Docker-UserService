<?php
date_default_timezone_set('America/Los_Angeles');
/**
 * This is the entry point file for the applicaiton and all request will be redirected to 
 * this file and application will be loaded after the execution of this file
 */
// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application').DIRECTORY_SEPARATOR);

// Define application environment
/**
 * @todo: If we are determining the server and location from which this request come from,
 * then we can do that logic over here and before toching the application itself we know the infomations
 * for translation, location wise redirect etc.
 * 
 * Here we are declaring an applicaiton constant so that our application know which environment is
 * currently active. This is used in config file as well
 */
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));


// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
//Application path
realpath(APPLICATION_PATH),
//Noobh Web framework path, You can even point it to your local if needed
realpath(APPLICATION_PATH . '/../library'),
get_include_path(),
)));
/**
 * Include auto loader
 */
include_once('Noobh/Loader/Autoloader.php');
/**
 * The following code can be added in application Bootstrap or t
 * index.php file where document root exist. By default all library
 * classes under Noobh will be autoloaded.
 */
$autoloader = Noobh_Loader_Autoloader::getInstance();
//$globalConfigs = array(GLOBAL_RESOURCE_PATH. '/conf/conf.php');
$config = Noobh_Config::getInstance(realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'application.ini'));

Noobh_Registry::set('config', $config);
/**
 * The following code can be added in application Bootstrap or
 * index.php file where document root exist. By default all library
 * classes under ISTWeb will be autoloaded.
 */
require_once (realpath(APPLICATION_PATH) . DIRECTORY_SEPARATOR . 'Bootstrap.php');
$bootstrap = new Bootstrap($config);
$bootstrap->load();