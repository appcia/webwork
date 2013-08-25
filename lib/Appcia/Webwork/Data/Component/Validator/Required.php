<?

namespace Appcia\Webwork\Data\Component\Validator;

use Appcia\Webwork\Data\Component\Validator;
use Appcia\Webwork\Data\Value;

class Required extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if (Value::isEmpty($value)) {
            return false;
        }

        if (Value::isArray($value)) {
            $flag = !empty($value);
        } else {
            $value = Value::getString($value);
            $flag = !Value::isEmpty($value);
        }

        return $flag;
    }

}