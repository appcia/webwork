<?

namespace Appcia\Webwork\Model;

/**
 * Tool for parameter mapping
 *
 * Usage: example value 'foo_{bar}', parameters are in braces
 *
 * @package Appcia\Webwork\Model
 */
class Pattern
{
    const PARAM_CLASS = '[A-Za-z0-9-]+';
    const PARAM_SUBSTITUTION = '___param___';

    /**
     * @var string
     */
    private $value;

    /**
     * @var array
     */
    private $params;

    /**
     * @var string
     */
    private $regexp;

    /**
     * @param string $value
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($value)
    {
        $this->setValue($value);
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
     * @return string|null
     */
    public function getRegExp()
    {
        return $this->regexp;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return Pattern
     * @throws \InvalidArgumentException
     */
    public function setValue($value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("Pattern value cannot be empty.");
        }

        $this->params = array();

        $match = array();
        if (preg_match_all('/\{(' . self::PARAM_CLASS . ')\}/', $value, $match)) {
            $params = $match[1];

            $regexp = preg_replace('/\{(' . self::PARAM_CLASS . ')\}/', self::PARAM_SUBSTITUTION, $value);
            $regexp = '/^' . preg_quote($regexp, '/') . '?$/';
            $regexp = str_replace(self::PARAM_SUBSTITUTION, '(' . self::PARAM_CLASS . ')', $regexp);

            $this->regexp = $regexp;
            $this->params = $params;
        } else {
            $this->regexp = '/^' . preg_quote($value, '/') . '?$/';
        }

        return $this;
    }
}