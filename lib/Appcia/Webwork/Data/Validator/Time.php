<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;

class Time extends Validator
{
    const HOURS = 'hh';
    const MINUTES = 'mm';
    const SECONDS = 'ss';

    /**
     * @var string
     */
    private $regexp;

    /**
     * Constructor
     *
     * @param string $format
     */
    public function __construct($format = 'hh:mm:ss')
    {
        $map = str_replace(
            array(self::HOURS, self::MINUTES, self::SECONDS),
            array('(0[0-9]|1[0-9]|2[0-3])', '([0-5][0-9])', '([0-5][0-9])'),
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

        $valid = preg_match($this->regexp, $value);

        return $valid;
    }

}