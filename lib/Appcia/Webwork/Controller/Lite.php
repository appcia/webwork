<?

namespace Appcia\Webwork\Controller;

use Appcia\Webwork\Web\App;

/**
 * Skeleton controller, only essentials
 *
 * @package Appcia\Webwork\Controller
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
        return $this->getApp()
            ->get($key);
    }
}