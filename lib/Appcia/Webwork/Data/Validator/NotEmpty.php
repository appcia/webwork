<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;

class NotEmpty extends Validator {

    /**
     * {@inheritdoc}
     */
    public function validate($value) {
        if (is_numeric($value) && floatval($value) === 0.0) {
            return false;
        }

        return !empty($value);
    }

}