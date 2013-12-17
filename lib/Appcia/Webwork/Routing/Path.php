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
     * Standard parameter classes
     */
    const DIGITS = '[:digit:]+';
    const ALPHABETIC = '[:alpha:]+';
    const ALPHANUMERIC = '[:alnum:]+';
    const FILENAME = '[\w- ]+(?:\.\w+)+';

    /**
     * Base route
     *
     * @var Route
     */
    protected $route;

    /**
     * Optional versions of path
     *
     * @var Template[]|null
     */
    protected $segments;

    /**
     * Constructor
     *
     * @param Route  $route   Base route
     * @param string $content Path template
     */
    public function __construct(Route $route, $content = null)
    {
        $this->route = $route;
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

        return $this;
    }

    /**
     * @return Template[]
     */
    public function getSegments()
    {
        if ($this->segments === null) {
            $this->findSegments();
        }

        return $this->segments;
    }

    /**
     * Process content taking into account optional parts
     *
     * @return $this
     */
    public function findSegments()
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
        $param = '___param___';
        
        for ($i = 0; $i < $count - 1; $i++) {
            $segments[$i] = str_replace('(' . $segments[$i + 1] . ')', $param, $segments[$i]);
            $segments[$i + 1] = str_replace($param, $segments[$i + 1], $segments[$i]);
            $segments[$i] = str_replace($param, '', $segments[$i]);
        }

        foreach ($segments as $key => $segment) {
            $segments[$key] = new Template($segment);
        }

        // Order by max parameter count (for best assembled path)
        usort($segments, function (Template $a, Template $b) {
            $c = count($a->getParams());
            $d = count($b->getParams());

            return ($c == $d)
                ? 0
                : ($c > $d)
                    ? -1
                    : 1;
        });

        $this->segments = $segments;

        return $this;
    }

    /**
     * Compile path to regular expression
     *
     * @return $this
     */
    protected function compileRegExp()
    {
        $exp = str_replace(array('(', ')'), array('(', ')?'), $this->content);
        foreach ($this->route->getParams() as $name => $config) {
            $class = isset($config['regExp'])
                ? $config['regExp']
                : self::PARAM;

            $exp = str_replace('{' . $name . '}', '(' . $class . ')', $exp);
        }
        $exp = ':^' . $exp . '$:u';

        $this->regExp = $exp;

        return $this;
    }
}