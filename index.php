<?

require_once 'bootstrap.php';
global $bootstrap;

$app = new \Appcia\Webwork\App($bootstrap);
$app->run();