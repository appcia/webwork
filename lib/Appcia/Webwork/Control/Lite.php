<?

namespace Appcia\Webwork\Control;

use Appcia\Webwork\Web\App;

/**
 * Fasade for application control, only essentials
 */
abstract class Lite
{
    /**
     * @var App
     */
    protected $app;

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
    public function getApp()
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
    public function get($key)
    {
        $service = $this->app->get($key);

        return $service;
    }
}