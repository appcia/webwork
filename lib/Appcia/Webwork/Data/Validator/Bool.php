<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;
use Appcia\Webwork\Exception\Exception;

class Bool extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if ($this->isEmptyValue($value)) {
            return true;
        }

        $valid = in_array($value, array(
            0, 1, '0', '1', false, true
        ), true);

        return $valid;
    }

}