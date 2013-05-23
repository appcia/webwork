<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;
use Appcia\Webwork\Exception\Exception;

class Length extends Validator
{

    /**
     * Minimum
     *
     * @var int
     */
    private $min;

    /**
     * Maximum
     *
     * @var int
     */
    private $max;

    /**
     * Constructor
     *
     * @param int $max Maximum
     * @param int $min Minimum
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($min = 0, $max = INF)
    {
        if ($min < 0 || $max < 0) {
            throw new \InvalidArgumentException('String length must be greater than zero');
        }

        $this->min = $min;
        $this->max = $max;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if ($value === '' || $value === null) {
            return true;
        }

        if (!is_scalar($value)) {
            return false;
        }

        $length = mb_strlen($value);

        return ($length >= $this->min && $length <= $this->max);
    }

}