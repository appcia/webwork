<?

namespace Appcia\Webwork\Data\Component\Validator;

use Appcia\Webwork\Data\Component\Validator;
use Appcia\Webwork\Data\Value;

class Integer extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if (Value::isEmpty($value)) {
            return true;
        }

        $valid = (filter_var($value, FILTER_VALIDATE_INT) !== false);

        return $valid;
    }

}