<?

namespace Appcia\Webwork\Data\Component\Validator;

use Appcia\Webwork\Data\Component\Validator;
use Appcia\Webwork\Data\Value;
use Appcia\Webwork\Exception\Exception;

class Bool extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if (Value::isEmpty($value)) {
            return true;
        }

        $valid = in_array($value, array(
            0, 1, '0', '1', false, true
        ), true);

        return $valid;
    }

}