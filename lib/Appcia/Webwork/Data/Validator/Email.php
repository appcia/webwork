<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;
use Appcia\Webwork\Data\Value;

class Email extends Validator
{
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

        $flag = (filter_var($value, FILTER_VALIDATE_EMAIL) !== false);

        return $flag;
    }

}