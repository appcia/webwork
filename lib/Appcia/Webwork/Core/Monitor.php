<?

namespace Appcia\Webwork\Core;

/**
 * Events monitor
 *
 * @package Appcia\Webwork\Core
 */
class Monitor
{
    /**
     * Watched object
     *
     * @var object
     */
    protected $watch;

    /**
     * Event list with listener callbacks
     *
     * @var array
     */
    protected $events;

    /**
     * Constructor
     *
     * @param object $watch  Object to be watched
     * @param array  $events Event names
     */
    public function __construct($watch, $events = array())
    {
        $this->setWatch($watch);
        $this->setEvents($events);
    }

    /**
     * Set events
     *
     * @param array $events Event names
     *
     * @return $this
     */
    public function setEvents(array $events)
    {
        $this->clearEvents();

        foreach ($events as $event) {
            $this->addEvent($event);
        }

        return $this;
    }

    /**
     * Clear all events
     *
     * @return $this
     */
    public function clearEvents()
    {
        $this->events = array();

        return $this;
    }

    /**
     * Get event names
     *
     * @return mixed
     */
    public function getEvents()
    {
        $events = array_keys($this->events);

        return $events;
    }

    /**
     * Add event
     *
     * @param string $event Event name
     *
     * @return $this
     * @throws \LogicException
     */
    public function addEvent($event)
    {
        if (array_key_exists($event, $this->events)) {
            throw new \LogicException(sprintf("Event '%s' already exists.", $event));
        }

        $this->events[$event] = array();

        return $this;
    }

    /**
     * Register event listener
     *
     * @param string   $event    Event
     * @param \Closure $callback Callback
     *
     * @return $this
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    public function listen($event, \Closure $callback)
    {
        if (!array_key_exists($event, $this->events)) {
            throw new \OutOfBoundsException(sprintf("Cannot listen non-existing event: '%s'.", $event));
        }

        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(sprintf("Monitor callback for event '%s' is invalid.", $event));
        }

        $this->events[$event][] = $callback;

        return $this;
    }

    /**
     * Notify listeners about event
     *
     * @param string $event Event
     *
     * @return $this
     * @throws \OutOfBoundsException
     */
    public function notify($event)
    {
        if (!array_key_exists($event, $this->events)) {
            throw new \OutOfBoundsException(sprintf("Cannot notify about non-existing event: '%s'.", $event));
        }

        foreach ($this->events[$event] as $callback) {
            call_user_func($callback, $this->watch);
        }

        return $this;
    }

    /**
     * Set watching object
     *
     * @param object $watch Object to be watched
     *
     * @return $this
     */
    public function setWatch($watch)
    {
        $this->watch = $watch;

        return $this;
    }

    /**
     * Get watched object
     *
     * @return object
     */
    public function getWatch()
    {
        return $this->watch;
    }
}