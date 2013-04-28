<?

namespace App;

use App\Entity\Manager as EntityManager;
use Appcia\Webwork\Module;
use Appcia\Webwork\Resource\Manager as ResourceManager;

class AppModule extends Module
{
    /**
     * @return AppModule
     */
    public function init()
    {
        // Configuration
        $config = $this->container->get('config');
        $config->loadFile('config/resources.php');
        $config->loadFile('config/settings.php');

        // Entity manager
        $module = $this;

        $this->container->single('em', function ($container) use ($module) {
            $bootstrap = $container->get('bootstrap');
            $devFlag = $bootstrap->getEnvironment() == "development";
            $config = $container->get('config');

            // Configuration
            $configuration = new \Doctrine\ORM\Configuration();

            // Cache
            $cache = new \Doctrine\Common\Cache\ArrayCache();

            $configuration->setMetadataCacheImpl($cache);
            $configuration->setQueryCacheImpl($cache);

            // Proxies
            $configuration->setAutoGenerateProxyClasses($devFlag);
            $configuration->setProxyDir('cache/proxy');
            $configuration->setProxyNamespace('Proxies');

            // Driver
            $paths = array(
                'lib/' . $module->getNamespace() . '/Entity'
            );

            $driverImpl = $configuration->newDefaultAnnotationDriver($paths);
            $configuration->setMetadataDriverImpl($driverImpl);

            $em = EntityManager::make($container, $config->get('db'), $configuration);

            return $em;
        });

        // Resource manager
        $this->container->single('rm', function ($container) {
            $session = $container->get('session');
            $rm = new ResourceManager($session);

            $container->get('config')
                ->grab('rm')
                ->inject($rm);

            return $rm;
        });

        return $this;
    }

    /**
     * @return AppModule
     */
    public function run()
    {
        return $this;
    }

}