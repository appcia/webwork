<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;

class Email extends Validator {

    /**
     * {@inheritdoc}
     */
    public function validate($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

}