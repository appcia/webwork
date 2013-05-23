<?

namespace Appcia\Webwork\Controller;

use Appcia\Webwork\Container;

abstract class Lite
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Constructor
     *
     * @param Container $container Container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get DI container
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
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
        return $this->container->get($key);
    }
}