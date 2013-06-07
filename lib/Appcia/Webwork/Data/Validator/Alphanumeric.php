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
        if ($this->isEmptyValue($value)) {
            return true;
        }

        $valid = ctype_alnum($value);

        return $valid;
    }

}