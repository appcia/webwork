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

        $value = $this->getStringValue($value);
        if ($value === null) {
            return false;
        }

        $flag = !empty($value);

        return $flag;
    }

}