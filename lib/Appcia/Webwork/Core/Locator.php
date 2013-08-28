<?

namespace Appcia\Webwork\Core;

use Appcia\Webwork\Core\Container;
use Appcia\Webwork\Storage\Config;

/**
 * Service locator registry
 * Anti-pattern, use only for vendor classes where passing DI container is difficult
 */
abstract class Locator
{
    /**
     * @var Container
     */
    protected static $app;

    /**
     * Setup application
     *
     * @param App $app
     *
     * @return App
     * @throws \ErrorException
     */
    public static function setup(App $app)
    {
        static::$app = $app;
    }

    /**
     * Get application
     *
     * @return App
     * @throws \ErrorException
     */
    public static function app()
    {
        if (static::$app === null) {
            throw new \ErrorException("Locator application is not specified.");
        }

        return static::$app;
    }

    /**
     * Bind for re-creation or set plain value
     *
     * @param string   $id    Identifier
     * @param callable $value Service
     */
    public static function bind($id, \Closure $value)
    {
        static::$app->set($id, $value);
    }

    /**
     * Register as singleton
     *
     * @param string   $id       Identifier
     * @param callable $callable Service
     */
    public static function single($id, \Closure $callable)
    {
        static::$app->single($id, $callable);
    }

    /**
     * Register service
     *
     * @param string $id    Identifier
     * @param mixed  $value Service
     */
    public static function set($id, $value)
    {
        static::$app->set($id, $value);
    }

    /**
     * Get bound or registered service
     *
     * @param string $id Identifier
     *
     * @return mixed
     */
    public static function get($id)
    {
        return static::$app->get($id);
    }
}