<?

namespace Appcia\Webwork;

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
        }
        else {
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
     * @throws Exception
     */
    public function getContext()
    {
        if ($this->context === null) {
            throw new Exception(sprintf("Cannot use component '%s' without any context", $this->name));
        }

        return $this->context;
    }

    /**
     * Set use context
     *
     * @param Context $context Context
     *
     * @return Component
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

}