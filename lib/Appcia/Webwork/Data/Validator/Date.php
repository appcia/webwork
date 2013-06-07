<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;

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
    private $format;

    /**
     * Constructor
     *
     * @param string $format
     */
    public function __construct($format = self::DATE_TIME)
    {
        $this->format = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if ($this->isEmptyValue($value)) {
            return true;
        }

        $value = $this->getStringValue($value);
        if ($value === null) {
            return false;
        }

        $date = \DateTime::createFromFormat($this->format, $value);
        $flag = ($date !== false);

        return $flag;
    }
}