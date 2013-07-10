<?

namespace Appcia\Webwork\Storage\Session\Handler;

use Appcia\Webwork\Storage\Session\Handler;

/**
 * Native PHP session handler
 *
 * @package Appcia\Webwork\Storage\Handler
 */
class Php extends Handler
{
    /**
     * Constructor
     */
    public function __construct()
    {
        session_start();
        $this->data = & $_SESSION;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        session_commit();
    }
}