<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;

class DateTime extends Validator
{
    /**
     * @var string
     */
    private $format;

    /**
     * @param string $format
     */
    public function __construct($format = null)
    {
        $this->format = 'Y-m-d H:i:s';

        if ($format !== null) {
            $this->format = $format;
        }
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

        $date = new \DateTime($value);
        $valid = ($date->format($this->format) == $value);

        return $valid;
    }
}