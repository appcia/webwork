<?

namespace Appcia\Webwork\Web\Request;

use Appcia\Webwork\Web\Request;

/**
 * Native PHP request
 *
 * @package Appcia\Webwork\Web
 */
class Php extends Request
{
    /**
     * Remove annoying magic quotes at runtime
     *
     * @return $this
     */
    protected function prepare()
    {
        if (get_magic_quotes_gpc()) {
            $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
            while (list($key, $val) = each($process)) {
                foreach ($val as $k => $v) {
                    unset($process[$key][$k]);
                    if (is_array($v)) {
                        $process[$key][stripslashes($k)] = $v;
                        $process[] = & $process[$key][stripslashes($k)];
                    } else {
                        $process[$key][stripslashes($k)] = stripslashes($v);
                    }
                }
            }
            unset($process);
        }

        return $this;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->prepare();

        $this->setPost($_POST);
        $this->setGet($_GET);
        $this->setFiles($_FILES);

        $this->setScriptFile($_SERVER['SCRIPT_NAME'])
            ->setServer($_SERVER['SERVER_NAME'])
            ->setMethod($_SERVER['REQUEST_METHOD'])
            ->setProtocol($_SERVER['SERVER_PROTOCOL'])
            ->setPort($_SERVER['SERVER_PORT'])
            ->setIp($_SERVER['REMOTE_ADDR']);

        if (isset($_SERVER['REQUEST_URI'])) {
            $this->setUri($_SERVER['REQUEST_URI']);
        } else {
            $this->setUri($_SERVER['PHP_SELF']);
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->setAjax($_SERVER['HTTP_X_REQUESTED_WITH'] == 'xmlhttprequest');
        }

        return $this;
    }
}