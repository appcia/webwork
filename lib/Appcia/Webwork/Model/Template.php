<?

namespace Appcia\Webwork\Model;

/**
 * Template for files, paths parameter mappings
 * Used in form field grouping, route parameter mapping, logger messages
 *
 * @package Appcia\Webwork\Model
 */
class Template
{
    const PARAM_CLASS = '[A-Za-z0-9-]+';
    const PARAM_SUBSTITUTION = '___param___';

    /**
     * Content with parameters in braces
     *
     * @var string
     */
    protected $content;

    /**
     * Parameter map
     *
     * @var array
     */
    protected $params;

    /**
     * Regular expression for parameters retrieving
     *
     * @var string
     */
    protected $regExp;

    /**
     * Constructor
     *
     * @param string $content For example: 'week_{num}_{day}', (parameters are in braces)
     */
    public function __construct($content = null)
    {
        $this->params = array();
        $this->setContent($content);
    }

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set parameters
     *
     * @param array $params
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setParams($params)
    {
        foreach ($params as $name => $value)
        {
            if (!array_key_exists($name, $this->params)) {
                throw new \InvalidArgumentException(sprintf("Template parameter '%s' does not exist.", $name));
            }

            $this->params[$name] = $value;
        }

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content
     *
     * @param string $content Content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = (string) $content;

        $this->processParams();
        $this->processRegExp();

        return $this;
    }

    /**
     * Set parameter value
     *
     * @param string $param Parameter name
     * @param        $value $value Value
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function set($param, $value)
    {
        if (!array_key_exists($param, $this->params)) {
            throw new \InvalidArgumentException(sprintf("Template parameter '%s' does not exist.", $param));
        }

        $this->params[$param] = $value;

        return $this;
    }

    /**
     * Get parameter value
     *
     * @param string $param Parameter name
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function get($param)
    {
        if (!array_key_exists($param, $this->params)) {
            throw new \InvalidArgumentException(sprintf("Template parameter '%s' does not exist.", $param));
        }

        return $this->params[$param];
    }

    /**
     * Inject parameter values into content
     *
     * @return string
     */
    public function render()
    {
        $names = array_keys($this->params);
        foreach ($names as $n => $name) {
            $names[$n] = '{' . $name . '}';
        }

        $values = array_values($this->params);
        $result = str_replace($names, $values, $this->content);

        return $result;
    }

    /**
     * Compile content to regular expression
     *
     * @return $this
     */
    protected function processRegExp()
    {
        $exp = preg_replace(':\{(' . self::PARAM_CLASS . ')\}:', '(' . self::PARAM_CLASS . ')', $this->content);
        $exp = ':^' . $exp . '$:';

        $this->regExp = $exp;

        return $this;
    }

    /**
     * Find parameters in content, format: 'Hello {name}!'
     *
     * @return $this
     */
    protected function processParams()
    {
        $params = array();
        $match = array();

        if (preg_match_all(':\{(' . self::PARAM_CLASS . ')\}:', $this->content, $match)) {
            foreach ($match[1] as $param) {
                $params[$param] = null;
            }
        }

        $this->params = $params;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegExp()
    {
        return $this->regExp;
    }
}