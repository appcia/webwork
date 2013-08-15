<?

namespace Appcia\Webwork\Routing;

use Appcia\Webwork\Model\Template;
use Appcia\Webwork\Storage\Config;

/**
 * Routing path template
 *
 * @package Appcia\Webwork\Routing
 */
class Path extends Template
{
    /**
     * @var string
     */
    protected $regExp;

    /**
     * @var array
     */
    protected $optionals;

    /**
     * Constructor
     *
     * @param string $content Path template
     */
    public function __construct($content = null)
    {
        $this->optionals = array();
        parent::__construct($content);
    }

    /**
     * Set path template
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
        parent::setContent($content);

        $this->processRegExp($content);

        return $this;
    }

    /**
     * Compile path to regular expression
     *
     * @param string $content Path template
     *
     * @return $this
     */
    protected function processRegExp($content)
    {
        $exp = str_replace(array('(', ')'), array('(', ')?'), $content);
        $exp = preg_replace(':\{(' . self::PARAM_CLASS . ')\}:', '(' . self::PARAM_CLASS . ')', $exp);
        $exp = ':^' . $exp . '$:';

        $this->regExp = $exp;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegExp()
    {
        return $this->regExp;
    }
}