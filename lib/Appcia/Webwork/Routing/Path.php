<?

namespace Appcia\Webwork\Routing;

use Appcia\Webwork\Model\Template;
use Appcia\Webwork\Storage\Config;

/**
 * Path
 *
 * @package Appcia\Webwork\Routing
 */
class Path extends Template
{
    /**
     * Set path
     *
     * @param string $content Path template
     *
     * @return $this
     */
    public function setContent($content)
    {
        if ($content !== '/') {
            $content = rtrim($content, '/');
        }

        return parent::setContent($content);
    }
}