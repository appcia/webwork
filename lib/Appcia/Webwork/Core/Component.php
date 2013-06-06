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
    private $name;

    /**
     * Use context
     *
     * @var Context
     */
    private $context;

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
    private function extractName()
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
     * Check whether value could be casted to string
     *
     * @param mixed $value Value
     *
     * @return boolean
     */
    protected function isStringifyable($value)
    {
        $flag = !(!is_scalar($value)
            && !(is_object($value) && method_exists($value, '__toString')));

        return $flag;
    }
}