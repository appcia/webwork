<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Contains extends Helper
{
    /**
     * Caller
     *
     * @param mixed $value Value
     * @param mixed $set   Set
     *
     * @return boolean
     */
    public function contains($value, $set)
    {
        if ($this->isEmptyValue($value) || !$this->isArrayValue($set)) {
            return false;
        }

        if (is_array($set)) {
            return in_array($value, $set);
        } else {
            foreach ($set as $val) {
                if ($value == $val) {
                    return true;
                }
            }
        }

        return false;
    }
}
