<?

namespace Appcia\Webwork\Util;

class Profiler
{
    /**
     * @var float
     */
    private $startTime;

    /**
     * @var int
     */
    private $startMemory;

    /**
     * Start measuring
     *
     * @return void
     */
    public function start()
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
    }

    /**
     * Get elapsed time
     *
     * @return int
     */
    public function getTimeElapsed()
    {
        return microtime(true) - $this->startTime;
    }

    /**
     * Get real memory usage
     *
     * @return int
     */
    public function getMemoryUsage()
    {
        return memory_get_usage() - $this->startMemory;
    }

    /**
     * Get memory from startup
     *
     * @return int
     */
    public function getStartMemory()
    {
        return $this->startMemory;
    }

    /**
     * Get time at startup
     *
     * @return float
     */
    public function getStartTime()
    {
        return $this->startTime;
    }


}