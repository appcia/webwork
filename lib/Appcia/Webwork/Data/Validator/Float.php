<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;
use Appcia\Webwork\Exception;

class Float extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if ($value === '' || $value === null) {
            return true;
        }

        $valid = (filter_var($value, FILTER_VALIDATE_FLOAT) !== false);

        return $valid;
    }

}