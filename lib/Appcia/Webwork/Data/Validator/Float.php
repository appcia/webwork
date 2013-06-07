<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;
use Appcia\Webwork\Exception\Exception;

class Float extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if ($this->isEmptyValue($value)) {
            return true;
        }

        $valid = (filter_var($value, FILTER_VALIDATE_FLOAT) !== false);

        return $valid;
    }

}