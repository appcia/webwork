<?

namespace Appcia\Webwork\Web;

use Appcia\Webwork\Storage\Session\Space;
use Appcia\Webwork\Web\Request;

/**
 * Stores previously visited URL's in session storage
 */
class Tracker
{
    /**
     * Data storage
     *
     * @var Space
     */
    protected $data;

    /**
     * Maximum tracked steps count
     *
     * @var int
     */
    protected $stepCount;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Constructor
     *
     * @param Space   $space   Session storage space
     * @param Request $request Request to track
     */
    public function __construct(Space $space, Request $request)
    {
        $space->setAutoflush(true);

        $this->data = $space;
        $this->request = $request;

        $this->stepCount = 10;
    }

    /**
     * Track current URL
     *
     * @return $this
     */
    public function track()
    {
        $steps = $this->getSteps();
        $steps[] = array(
            'url' => $this->request->getUri(),
            'time' => time()
        );

        $steps = array_slice($steps, $this->stepCount * (-1));

        $this->data['steps'] = $steps;

        return $this;
    }

    /**
     * Get first previous URL that differs to current
     *
     * @return string|null
     */
    public function getPreviousUrl()
    {
        $currentUrl = $this->getCurrentUrl();

        $steps = (array) $this->data['steps'];
        foreach (array_reverse($steps) as $step) {
            if ($step['url'] !== $currentUrl) {
                return $step['url'];
            }
        }

        return null;
    }

    /**
     * Get URL from previous request
     *
     * @return string
     */
    public function getLastUrl()
    {
        $step = $this->getLastStep();
        if ($step === null) {
            return null;
        }

        $url = (string) $step['url'];

        return $url;
    }

    /**
     * Get last tracked data
     *
     * @return array|null
     */
    public function getLastStep()
    {
        if (empty($this->data['steps'])) {
            return null;
        }

        $steps = $this->data['steps'];
        $lastStep = end($steps);

        return $lastStep;
    }

    /**
     * Get tracked steps
     *
     * @return array
     */
    public function getSteps()
    {
        if (!isset($this->data['steps'])) {
            $this->data['steps'] = array();
        }

        $steps = $this->data['steps'];

        return $steps;
    }

    /**
     * Get last time when made a request
     *
     * @return int
     */
    public function getLastTime()
    {
        $step = $this->getLastStep();
        if ($step === null) {
            return null;
        }

        $time = (int) $step['time'];

        return $time;
    }

    /**
     * Get delay time after last request
     *
     * @return int
     */
    public function getDelayTime()
    {
        $time = time();
        $delay = $time - $this->getLastTime();

        return $delay;
    }

    /**
     * Get current URL
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        $url = $this->request->getUri();

        return $url;
    }

    /**
     * Clear stored data
     *
     * @return $this
     */
    public function clearData()
    {
        $this->data['steps'] = array();

        return $this;
    }

    /**
     * Get data storage
     *
     * @return Space
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get source request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set steps count to be tracked
     *
     * @return int
     */
    public function getStepCount()
    {
        return $this->stepCount;
    }

    /**
     * Set steps count to be tracked
     *
     * @param int $count Count
     *
     * @return $this
     */
    public function setStepCount($count)
    {
        $this->stepCount = $count;

        return $this;
    }
}