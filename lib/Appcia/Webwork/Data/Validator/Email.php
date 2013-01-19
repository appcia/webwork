<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;

class Email extends Validator {

    /**
     * {@inheritdoc}
     */
    public function validate($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

}