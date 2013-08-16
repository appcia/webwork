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
     * Get regular expression that match all segments
     *
     * @var string
     */
    protected $regExp;


    /**
     * Optional versions of path
     *
     * @var Template[]
     */
    protected $segments;

    /**
     * Constructor
     *
     * @param string $content Path template
     */
    public function __construct($content = null)
    {
        $this->segments = array();
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
        $this->processSegments();

        return $this;
    }

    /**
     * Compile path to regular expression
     *
     * @return $this
     */
    protected function processRegExp()
    {
        $exp = str_replace(array('(', ')'), array('(', ')?'), $this->content);
        $exp = preg_replace(':\{(' . self::PARAM_CLASS . ')\}:', '(' . self::PARAM_CLASS . ')', $exp);
        $exp = ':^' . $exp . '$:';

        $this->regExp = $exp;

        return $this;
    }

    /**
     * Process content taking into account optional parts
     *
     * @return $this
     */
    public function processSegments()
    {
        $path = '(' . $this->content . ')';
        $segments = array();

        do {
            $match = array();

            if (preg_match('/\(.*\)/', $path, $match)) {
                $path = $match[0];
                $path = substr($path, 1, -1);

                $segments[] = $path;
            } else {
                $path = null;
            }
        } while ($path !== null);

        $count = count($segments);
        for ($i = 0; $i < $count - 1; $i++) {
            $segments[$i] = str_replace('(' . $segments[$i + 1] . ')', self::PARAM_SUBSTITUTION, $segments[$i]);
            $segments[$i + 1] = str_replace(self::PARAM_SUBSTITUTION, $segments[$i + 1], $segments[$i]);
            $segments[$i] = str_replace(self::PARAM_SUBSTITUTION, '', $segments[$i]);
        }

        foreach ($segments as $key => $segment) {
            $segments[$key] = new Template($segment);
        }

        $this->segments = $segments;

        return $this;
    }

    /**
     * @return Template[]
     */
    public function getSegments()
    {
        return $this->segments;
    }
}