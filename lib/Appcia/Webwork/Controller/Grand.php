<?

namespace Appcia\Webwork\Controller;

use Appcia\Webwork\Exception\Error;
use Appcia\Webwork\Exception\NotFound;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\View\View;
use Appcia\Webwork\Web\Context;
use Appcia\Webwork\Web\Request;
use Appcia\Webwork\Web\Response;

/**
 * Most powerful controller which uses pre-defined keys in container
 *
 * @package Appcia\Webwork\Controller
 */
abstract class Grand extends Fat
{
    /**
     * Translate a message ID into localized text
     *
     * @param string $id Message ID
     *
     * @return mixed
     */
    protected function translate($id)
    {
        return $this->get('translator')
            ->translate($id);
    }

    /**
     * Go to previous tracked URL
     */
    protected function goBack()
    {
        $url = $this->get('tracker')
            ->getPreviousUrl();

        $this->goRedirect($url);
    }
}