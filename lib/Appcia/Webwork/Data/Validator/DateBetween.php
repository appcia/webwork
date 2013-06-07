<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Form\Field;
use Appcia\Webwork\Data\Validator;

/**
 * Check whether date belongs to range
 *
 * Value could be negated
 *
 * @package Appcia\Webwork\Data\Validator
 */
class DateBetween extends Validator
{
    /**
     * @var mixed
     */
    protected $left;

    /**
     * @var mixed
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
     * @param \DateTime|string $left   Date or time
     * @param \DateTime|string $right  Date or time
     * @param boolean          $edges  Include or exclude interval edges
     */
    public function __construct($left, $right, $edges = true)
    {
        $this->left = $this->getDateValue($left);
        $this->right = $this->getDateValue($right);
        $this->edges = $edges;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if ($this->isEmptyValue($value)) {
            return true;
        }

        $value = $this->getDateValue($value);
        if ($value === null) {
            return false;
        }

        $v = $value->getTimestamp();
        $l = $this->left === null ? -INF : $this->left->getTimestamp();
        $r = $this->right === null ? INF : $this->right->getTimestamp();

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