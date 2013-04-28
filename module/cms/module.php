<?

namespace Cms;

use Appcia\Webwork\Module;
use Appcia\Webwork\Config;
use Appcia\Webwork\Dispatcher;
use Appcia\Webwork\Util\Flash;
use Cms\Util\Auth;

class CmsModule extends Module
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $bootstrap = $this->container->get('bootstrap');
        $dispatcher = $this->container->get('dispatcher');

        // Configuration
        $config = $this->container->get('config');
        $config->loadFile($this->getPath() . '/config/routes.php');

        // Auth
        $this->container->single('auth', function ($container) {
            $em = $container->get('em');
            $session = $container->get('session');
            $auth = new Auth($em, $session, 'auth');

            $container->get('config')
                ->grab('auth')
                ->inject($auth);

            return $auth;
        });

        $dispatcher->addListener(Dispatcher::FIND_ROUTE, function ($container) {
            $dispatcher = $container->get('dispatcher');
            $router = $container->get('router');
            $auth = $container->get('auth');

            $currentRoute = $dispatcher->getRoute();

            if ($currentRoute->getModule() == 'cms' && !$auth->isAuthorized()) {
                $loginRoute = $router->getRoute('cms-user-login');
                $dispatcher->setRoute($loginRoute);
            }
        });

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        // Configuration
        $config = $this->container->get('config');
        $config->loadFile($this->getPath() . '/config/settings.php');

        // Flash
        $this->container->single('flash', function ($container) {
            $session = $container->get('session');

            $flash = new Flash($session);

            $container->get('config')
                ->grab('flash')
                ->inject($flash);

            return $flash;
        });

        return $this;
    }
}