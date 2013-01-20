<?

namespace Appcia\Webwork;

abstract class Listener {

    /**
     * @var Container
     */
    protected $container;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * Handle event, called by listener
     *
     * @param string $event Event name
     * @return mixed
     */
    abstract public function notify($event);
}