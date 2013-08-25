<?

namespace Appcia\Webwork\Data\Component\Validator;

use Appcia\Webwork\Data\Component\Validator;
use Appcia\Webwork\Data\Value;
use Appcia\Webwork\Exception\Exception;

class Length extends Validator
{

    /**
     * Minimum
     *
     * @var int
     */
    protected $min;

    /**
     * Maximum
     *
     * @var int
     */
    protected $max;

    /**
     * Constructor
     *
     * @throws \InvalidArgumentException
     */
    public function __construct()
    {
        $args = func_get_args();

        switch (count($args)) {
            case 1:
                $this->min = $args[0];
                $this->max = $args[0];
                break;
            case 2:
                $this->min = $args[0];
                $this->max = $args[1];
                break;
            default:
                throw new \InvalidArgumentException('Length validator parameter count should be 1 or 2.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if (Value::isEmpty($value)) {
            return true;
        }

        $value = Value::getString($value);
        if ($value === null) {
            return false;
        }

        $length = mb_strlen($value);

        $flag = ($length >= $this->min)
            && ($length <= $this->max);

        return $flag;
    }

}