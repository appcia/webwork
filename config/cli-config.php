<?

require_once (__DIR__ . '/../bootstrap.php');

global $bootstrap;

// Doctrine
$em = $bootstrap->getContainer()
    ->get('em');

return new \Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));

