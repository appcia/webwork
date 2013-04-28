<?

namespace Site\Controller;

use Appcia\Webwork\Controller;

class ErrorController extends Controller
{
    /**
     * @return array
     */
    public function errorAction()
    {
        return array(
            'error' => $this->get('exception')
        );
    }

    /**
     * @return array
     */
    public function notFoundAction()
    {
        return array();
    }
}