<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Core\Monitor;
use Appcia\Webwork\Storage\Config;

/**
 * Resource service
 *
 * @package Appcia\Webwork\Resource
 */
abstract class Service {

    /**
     * Monitor events
     */
    const BEFORE = 'before';
    const AFTER = 'after';

    protected static $events = array(
        self::BEFORE,
        self::AFTER
    );

    /**
     * Manager
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
     * Configuration
     *
     * @var array
     */
    protected $config;

    /**
     * Get monitor events
     *
     * @return array
     */
    public static function getEvents()
    {
        return static::$events;
    }

    /**
     * Creator
     *
     * @param mixed $config Config data
     *
     * @return mixed
     */
    public static function create($config)
    {
        return Config::create($config, get_called_class());
    }

    /**
     * Constructor
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
        $this->monitor = new Monitor($this, static::$events);
        $this->config = array();
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
     * Get manager
     *
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
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
     * Set configuration
     *
     * @param mixed $config
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get configuration
     *
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
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