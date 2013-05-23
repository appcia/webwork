<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;
use Appcia\Webwork\Exception\Exception;

class Alphanumeric extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if ($value === '' || $value === null) {
            return true;
        }

        $valid = ctype_alnum($value);

        return $valid;
    }

}