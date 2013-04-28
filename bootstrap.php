<?

// Environment
$env = getenv('APPLICATION_ENV');
$rootPath = dirname(__FILE__);
chdir($rootPath);

$loader = require 'vendor/autoload.php';
$configFile = 'config/' . $env . '/settings.php';

// Framework
$bootstrap = new \Appcia\Webwork\Bootstrap($env, $rootPath, $configFile, $loader);
$bootstrap->init();