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
    protected $map;

    /**
     * Regexp for parameter values extraction
     *
     * @var string
     */
    protected $regexp;

    /**
     * Constructor
     *
     * @param string $content For example: 'week_{num}_{day}', (parameters are in braces)
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($content)
    {
        $this->setContent($content);
    }

    /**
     * Get parameter names
     *
     * @return array
     */
    public function getParams()
    {
        $params = array_keys($this->map);
        
        return $params;
    }

    /**
     * Get parameter map
     *
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * @return string|null
     */
    public function getRegExp()
    {
        return $this->regexp;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $value For example: 'week_{num}_{day}', (parameters are in braces)
     *
     * @return Template
     * @throws \InvalidArgumentException
     */
    public function setContent($value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("Template content cannot be empty.");
        }
        if (!is_string($value)) {
            throw new \InvalidArgumentException("Template content should be a string.");
        }

        $this->map = array();

        $match = array();
        if (preg_match_all('/\{(' . self::PARAM_CLASS . ')\}/', $value, $match)) {
            $params = $match[1];

            $regexp = preg_replace('/\{(' . self::PARAM_CLASS . ')\}/', self::PARAM_SUBSTITUTION, $value);
            $regexp = '/^' . preg_quote($regexp, '/') . '?$/';
            $regexp = str_replace(self::PARAM_SUBSTITUTION, '(' . self::PARAM_CLASS . ')', $regexp);

            $this->regexp = $regexp;

            foreach ($params as $param) {
                $this->map[$param] = null;
            }
        } else {
            $this->regexp = '/^' . preg_quote($value, '/') . '?$/';
        }

        $this->content = $value;

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
        if (!array_key_exists($param, $this->map)) {
            throw new \InvalidArgumentException(sprintf("Template parameter '%s' does not exist.", $param));
        }

        $this->map[$param] = $value;

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
        if (!array_key_exists($param, $this->map)) {
            throw new \InvalidArgumentException(sprintf("Template parameter '%s' does not exist.", $param));
        }

        return $this->map[$param];
    }

    /**
     * @return string
     */
    public function render()
    {
        $names = array_keys($this->map);
        foreach ($names as $n => $name) {
            $names[$n] = '{' . $name . '}';
        }

        $values = array_values($this->map);
        $result = str_replace($names, $values, $this->content);

        return $result;
    }
}