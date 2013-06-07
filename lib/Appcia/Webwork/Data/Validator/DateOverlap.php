<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Form\Field;
use Appcia\Webwork\Data\Validator;

/**
 * Check whether two date ranges do not overlaps itself
 *
 * @package Appcia\Webwork\Data\Validator
 */
class DateOverlap extends DateBetween
{
    /**
     * {@inheritdoc}
     */
    public function validate($values)
    {
        if (!is_array($values) || count($values) !== 2) {
            return false;
        }

        if ($this->isEmptyValue($values[0]) && $this->isEmptyValue($values[1])) {
            return true;
        }

        $left = $this->getDateValue($values[0]);
        $right = $this->getDateValue($values[1]);

        $between = new DateBetween($left, $right);

        $flag = $between->validate($this->left)
            || $between->validate($this->right);

        return $flag;
    }
}