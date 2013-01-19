<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;

class NotEmpty extends Validator {

    /**
     * {@inheritdoc}
     */
    public function validate($data) {
        return !empty($data);
    }

}