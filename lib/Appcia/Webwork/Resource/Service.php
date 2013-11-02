<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Core\Monitor;
use Appcia\Webwork\Core\Object;
use Appcia\Webwork\Core\Objector;

/**
 * Resource service
 *
 * @package Appcia\Webwork\Resource
 */
abstract class Service implements Object
{
    /**
     * Monitor events
     */
    const BEFORE = 'before';
    const AFTER = 'after';

    /**
     * Resource Manager
     *
     * @var Manager
     */
    protected $manager;

    /**
     * Event monitor
     *
     * @var Monitor
     */
    protected $monitor;

    /**
     * Constructor
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
        $this->monitor = new Monitor($this, static::getEvents());
    }

    /**
     * Get possible monitor events
     *
     * @return array
     */
    public static function getEvents()
    {
        return array(
            self::BEFORE,
            self::AFTER
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function objectify($data, $args = array())
    {
        return Objector::objectify($data, $args, get_called_class());
    }

    /**
     * Get manager
     *
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Set manager
     *
     * @param Manager $manager
     *
     * @return $this
     */
    public function setManager(Manager $manager)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * Get event monitor
     *
     * @return Monitor
     */
    public function getMonitor()
    {
        return $this->monitor;
    }

    /**
     * Run with basic events support
     *
     * @return mixed
     */
    public function run()
    {
        $this->monitor->notify(self::BEFORE);
        $data = $this->call();
        $this->monitor->notify(self::AFTER);

        return $data;
    }

    /**
     * Call right service
     *
     * @return mixed
     */
    abstract protected function call();
}