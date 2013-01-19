<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;

class Time extends Validator {

    /**
     * {@inheritdoc}
     */
    public function validate($value) {
        return preg_match('/^(([0-1][0-9])|([2][0-3])):([0-5][0-9]):([0-5][0-9])$/', $value);
    }

}