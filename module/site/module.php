<?

namespace Site;

use Appcia\Webwork\Module;
use Appcia\Webwork\Config;
use Appcia\Webwork\Util\Flash;

class SiteModule extends Module
{
    /**
     * @return CmsModule
     */
    public function init()
    {
        $bootstrap = $this->container->get('bootstrap');

        // Configuration
        $config = new Config();
        $config->loadFile($this->getPath() . '/config/' . $bootstrap->getEnvironment() . '/settings.php');
        $config->loadFile($this->getPath() . '/config/routes.php');

        $this->container->get('config')
            ->extend($config);

        return $this;
    }

    /**
     * @return SiteModule
     */
    public function run()
    {
        // Flash
        $this->container->single('flash', function ($container) {
            $session= $container->get('session');

            $flash = new Flash($session);

            $container->get('config')
                ->grab('flash')
                ->inject($flash);

            return $flash;
        });

        return $this;
    }
}