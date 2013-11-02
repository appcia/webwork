<?

namespace Appcia\Webwork\Data;

use Appcia\Webwork\Core\Context;
use Appcia\Webwork\Core\Object;
use Appcia\Webwork\Core\Objector;

/**
 * Context related object
 * Base for view helpers, data forms, validators and filters
 */
abstract class Component implements Object
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
     * {@inheritdoc}
     */
    public static function objectify($data, $args = array())
    {
        return Objector::objectify($data, $args, get_called_class());
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