<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;
use Appcia\Webwork\Exception;

class Integer extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        $valid = (filter_var($value, FILTER_VALIDATE_INT) !== false);

        return $valid;
    }

}