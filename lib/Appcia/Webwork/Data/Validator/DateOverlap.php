<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Form\Field;
use Appcia\Webwork\Data\Validator;
use Appcia\Webwork\Data\Value;

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

        if (Value::isEmpty($values[0]) && Value::isEmpty($values[1])) {
            return true;
        }

        $left = Value::getDate($values[0]);
        $right = Value::getDate($values[1]);

        $between = new DateBetween($left, $right);

        $flag = $between->validate($this->left)
            || $between->validate($this->right);

        return $flag;
    }
}