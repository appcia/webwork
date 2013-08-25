<?

namespace Appcia\Webwork\Data;

use Appcia\Webwork\Core\Object;
use Appcia\Webwork\Core\Context;

/**
 * Context related object
 * Base for view helpers, data forms, validators and filters
 *
 * @package Appcia\Webwork\Core
 */
abstract class Component extends Object
{
    /**
     * Use context
     *
     * @var Context
     */
    protected $context;

    /**
     * Constructor
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->setContext($context);
    }

    /**
     * Get use context
     *
     * @return Context
     */
    public function getContext()
    {
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
}