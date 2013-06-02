<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;

class Date extends Validator
{
    const YEAR = 'yyyy';
    const MONTH = 'mm';
    const DAY = 'dd';

    /**
     * @var string
     */
    private $regexp;

    /**
     * Constructor
     *
     * @param string $format
     */
    public function __construct($format = 'yyyy-mm-dd')
    {
        $map = str_replace(
            array(self::YEAR, self::MONTH, self::DAY),
            array('(\d{4})', '(\d{2})', '(\d{2})'),
            $format
        );

        $this->regexp = '/^' . $map . '$/';
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if ($value === '' || $value === null) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        $parts = array();

        if (preg_match($this->regexp, $value, $parts)) {
            return checkdate($parts[2], $parts[3], $parts[1]);
        } else {
            return false;
        }
    }
}