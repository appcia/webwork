<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;
use Appcia\Webwork\Data\Value;

class Date extends Validator
{
    /**
     * Common used formats
     */
    const DATE_TIME = 'Y-m-d H:i:s';
    const DATE_TIME_SHORT = 'Y-m-d H:i';
    const DATE = 'Y-m-d';
    const TIME  = 'H:i:s';
    const TIME_SHORT = 'H:i';

    /**
     * @var string
     */
    protected $format;

    /**
     * Constructor
     *
     * @param string $format
     */
    public function __construct($format = null)
    {
        $this->format = $format;
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

        $flag = null;
        if ($this->format === null) {
            $flag = (strtotime($value) !== false);
        } else {
            $flag = (\DateTime::createFromFormat($this->format, $value) !== false);
        }

        return $flag;
    }
}