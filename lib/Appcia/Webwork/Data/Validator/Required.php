<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;

class Required extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if ($this->isEmptyValue($value)) {
            return false;
        }

        if ($this->isArrayValue($value)) {
            $flag = !empty($value);
        } else {
            $value = $this->getStringValue($value);
            $flag = !empty($value);
        }

        return $flag;
    }

}