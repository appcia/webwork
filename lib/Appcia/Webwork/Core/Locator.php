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
    protected static $container;

    /**
     * Setup instance
     *
     * @param Container $container
     *
     * @return void
     */
    public static function setup($container = null)
    {
        if ($container === null) {
            $container = new Container();
        }

        static::$container = $container;
    }

    /**
     * Bind for re-creation or set plain value
     *
     * @param string   $id    Identifier
     * @param callable $value Service
     */
    public static function bind($id, \Closure $value)
    {
        static::$container->set($id, $value);
    }

    /**
     * Register as singleton
     *
     * @param string   $id       Identifier
     * @param callable $callable Service
     */
    public static function single($id, \Closure $callable)
    {
        static::$container->single($id, $callable);
    }

    /**
     * Register service
     *
     * @param string $id    Identifier
     * @param mixed  $value Service
     */
    public static function set($id, $value)
    {
        static::$container->set($id, $value);
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
        return static::$container->get($id);
    }
}