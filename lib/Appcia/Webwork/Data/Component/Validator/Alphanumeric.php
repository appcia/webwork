<?

namespace Appcia\Webwork\Data\Component\Validator;

use Appcia\Webwork\Data\Component\Validator;
use Appcia\Webwork\Data\Value;
use Appcia\Webwork\Exception\Exception;

class Alphanumeric extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if (Value::isEmpty($value)) {
            return true;
        }

        $valid = ctype_alnum($value);

        return $valid;
    }

}