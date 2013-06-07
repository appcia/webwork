<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;

class Email extends Validator
{

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if ($this->isEmptyValue($value)) {
            return true;
        }

        $value = $this->getStringValue($value);
        if ($value === null) {
            return false;
        }

        $result = filter_var($value, FILTER_VALIDATE_EMAIL);

        return $result;
    }

}