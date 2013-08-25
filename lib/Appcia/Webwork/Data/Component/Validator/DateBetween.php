<?

namespace Appcia\Webwork\Data\Component\Validator;

use Appcia\Webwork\Web\Form\Field;
use Appcia\Webwork\Data\Component\Validator;
use Appcia\Webwork\Data\Value;

/**
 * Check whether date belongs to range
 *
 * Value could be negated
 *
 * @package Appcia\Webwork\Data\Component\Validator
 */
class DateBetween extends Validator
{
    /**
     * @var int|null
     */
    protected $left;

    /**
     * @var int|null
     */
    protected $right;

    /**
     * Include / exclude interval edges
     *
     * @var boolean
     */
    protected $edges;

    /**
     * Constructor
     *
     * @param mixed   $left   Date or time
     * @param mixed   $right  Date or time
     * @param boolean $edges  Include or exclude interval edges
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct($left, $right, $edges = true)
    {
        $left = Value::getDate($left);
        if ($left === null) {
            throw new \InvalidArgumentException("Date between left edge of interval is invalid.");
        }

        $right = Value::getDate($right);
        if ($right === null) {
            throw new \InvalidArgumentException("Date between right edge of interval is invalid.");
        }
        
        $this->left = $left;
        $this->right = $right;
        $this->edges = $edges;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if (Value::isEmpty($value)) {
            return true;
        }

        $value = Value::getDate($value);
        if ($value === null) {
            return false;
        }

        $v = $value->getTimestamp();
        $l = $this->left === null ? -INF : $this->left;
        $r = $this->right === null ? INF : $this->right;

        $flag = null;
        if ($this->edges) {
            $flag = ($v - $l >= 0) && ($v - $r <= 0)
                || ($v - $l <= 0) && ($v - $r >= 0);
        } else {
            $flag = ($v - $l > 0) && ($v - $r < 0)
                || ($v - $l < 0) && ($v - $r > 0);
        }

        return $flag;
    }
}