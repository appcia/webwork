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
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content For example: 'week_{num}_{day}', (parameters are in braces)
     *
     * @return Template
     * @throws \InvalidArgumentException
     */
    public function setContent($content)
    {
        $content = (string) $content;

        $this->content = $content;
        $this->processParams($content);

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
     * @param string $content
     *
     * @return $this
     */
    protected function processParams($content)
    {
        $params = array();
        $match = array();

        if (preg_match_all(':\{(' . self::PARAM_CLASS . ')\}:', $content, $match)) {
            foreach ($match[1] as $param) {
                $params[$param] = null;
            }
        }

        $this->params = $params;

        return $this;
    }
}