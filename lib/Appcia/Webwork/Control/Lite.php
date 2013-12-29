<?

namespace Appcia\Webwork\Control;

use Appcia\Webwork\Web\App;

/**
 * Skeleton control, only essentials
 */
abstract class Lite
{
    /**
     * @var App
     */
    private $app;

    /**
     * Constructor
     *
     * @param App $app Application
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Get application
     *
     * @return App
     */
    protected function getApp()
    {
        return $this->app;
    }

    /**
     * Get service or parameter from DI container
     *
     * @param string $key Service or parameter key
     *
     * @return mixed
     */
    protected function get($key)
    {
        $service = $this->app->get($key);

        return $service;
    }
}