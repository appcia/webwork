<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Form\Field;
use Appcia\Webwork\Data\Validator;

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
     * @param \Closure $callback Callback
     */
    public function __construct(\Closure $callback)
    {
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