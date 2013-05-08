<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;

class Date extends Validator
{

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if (!is_string($value)) {
            return false;
        }

        $parts = explode('-', str_replace('/', '-', $value));

        if (count($parts) !== 3) {
            return false;
        }

        list ($year, $month, $day) = $parts;

        return checkdate($month, $day, $year);
    }

}