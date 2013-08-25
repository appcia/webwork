<?

namespace Appcia\Webwork\Data\Component\Validator;

use Appcia\Webwork\Web\Form\Field;
use Appcia\Webwork\Data\Component\Validator;
use Appcia\Webwork\Web\Context;

class Callback extends Validator
{
    /**
     * Callback
     *
     * @var \Closure
     */
    protected $callback;

    /**
     * Constructor
     *
     * @param callable $callback Callback
     */
    public function __construct(Context $context, \Closure $callback)
    {
        parent::__construct($context);

        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        $flag = call_user_func($this->callback, $value);

        return $flag;
    }
}