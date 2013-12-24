<?

namespace Appcia\Webwork\Core\Exception;

use Appcia\Webwork\Web\Response;

class Handler {

    /**
     * Exception handlers
     *
     * @var array
     */
    protected $handlers;

    /**
     * Original error handler
     *
     * @var \Closure|NULL
     */
    protected $origin;

    /**
     * Enable or disable exception triggering on error
     *
     * @param boolean $flag Toggle
     *
     * @return $this
     */
    public function register($flag)
    {
        if (!$flag && $this->origin) {
            set_error_handler($this->origin);
        }

        if ($flag) {
            $this->origin = set_error_handler(array($this, 'execute'));
        }

        return $this;
    }

    /**
     * Callback for throwing exception on error
     *
     * @param int    $no      Error number
     * @param string $message Error Message
     * @param string $path    File path
     * @param int    $line    Line number
     *
     * @throws \ErrorException
     */
    public function execute($no, $message, $path, $line)
    {
        throw new \ErrorException($message, $no, 0, $path, $line);
    }

    /**
     * Register exception handler
     *
     * Exception could be:
     * - class name for example: Appcia\Webwork\NotFound
     * - object     for example: new Appcia\Webwork\Exception\NotFound()
     * - boolean    if should always / never handle any type of exception
     *
     * @param mixed    $exception Exception to be handled, see description!
     * @param callable $callback  Callback function
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function handle($exception, \Closure $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Handler callback is invalid');
        }

        if (is_object($exception)) {
            if (!$exception instanceof \Exception) {
                throw new \InvalidArgumentException('Invalid exception to be handled');
            }

            $exception = get_class($exception);
        }

        $this->handlers[] = array(
            'exception' => $exception,
            'callback' => $callback
        );

        return $this;
    }

    /**
     * React when exception occurred
     *
     * @param \Exception $e Exception
     *
     * @return Response
     * @throws \Exception
     */
    public function react($e)
    {
        // Look for most detailed exception handler
        $exception = get_class($e);
        $specificHandler = null;
        $allHandler = null;

        foreach ($this->handlers as $handler) {
            if ($handler['exception'] === true) {
                $allHandler = $handler;
            }

            if ($handler['exception'] === $exception) {
                $specificHandler = $handler;
            }
        }

        $handler = $allHandler;
        if ($specificHandler !== null) {
            $handler = $specificHandler;
        }

        if ($handler === null) {
            throw $e;
        }

        // Call for new response
        $response = call_user_func($handler['callback'], $this);

        if (!$response instanceof Response) {
            throw new \ErrorException('Error handler callback should return response object.');
        }

        return $response;
    }
}