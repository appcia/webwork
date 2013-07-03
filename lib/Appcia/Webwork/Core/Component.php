<?

namespace Appcia\Webwork\Core;

use Appcia\Webwork\Web\Context;

/**
 * Object related with web context
 * Base for view helpers, data validators and filters
 *
 * @package Appcia\Webwork\Core
 */
abstract class Component
{
    /**
     * Name
     *
     * @var string
     */
    protected $name;

    /**
     * Use context
     *
     * @var Context
     */
    protected $context;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->name = $this->extractName();
    }

    /**
     * Extract component name from class
     *
     * @return string
     */
    protected function extractName()
    {
        $class = get_class($this);
        $name = null;

        $pos = strrpos($class, '\\');
        if ($pos === false) {
            $name = $class;
        } else {
            $name = mb_substr($class, $pos + 1);
        }

        $name = lcfirst($name);

        return $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get use context
     *
     * @return Context
     * @throws \LogicException
     */
    public function getContext()
    {
        if ($this->context === null) {
            throw new \LogicException(sprintf("Component '%s' is not usable without any context", $this->name));
        }

        return $this->context;
    }

    /**
     * Set use context
     *
     * @param Context $context Context
     *
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Check whether value seems to be empty
     *
     * @param $value
     *
     * @return boolean
     */
    protected function isEmptyValue($value)
    {
        $flag = ($value === '')
            || ($value === null);

        return $flag;
    }

    /**
     * Check whether values could be iterated with foreach loop, accessed like an array
     *
     * @param mixed $value Value
     *
     * @return boolean
     */
    protected function isArrayValue($value)
    {
        $flag =  is_array($value)
            || (($value instanceof \Traversable) && ($value instanceof \ArrayAccess));

        return $flag;
    }

    /**
     * Get value treated as string
     *
     * @param mixed $value Value
     *
     * @return string|null
     */
    protected function getStringValue($value)
    {
        $flag = !(!is_scalar($value)
            && !(is_object($value) && method_exists($value, '__toString')));

        if ($flag) {
            $value = (string) $value;
        } else {
            $value = null;
        }

        return $value;
    }

    /**
     * Get value treated as date time object
     *
     * @param mixed $value
     *
     * @return \DateTime|null
     */
    protected function getDateValue($value)
    {
        if ($value instanceof \DateTime) {
            return $value;
        }

        $value = $this->getStringValue($value);

        try {
            $value = new \DateTime($value);
        } catch (\Exception $e) {
            return null;
        }

        return $value;
    }
}