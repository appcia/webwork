<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;
use Appcia\Webwork\Data\Form\Field;

class Same extends Validator
{
    /**
     * Base field
     */
    private $base;

    /**
     * Dependent field
     */
    private $dependent;

    /**
     * Constructor
     *
     * @param Field $base      Base field
     * @param Field $dependent Dependent field
     */
    public function __construct(Field $base, Field $dependent)
    {
        $this->base = $base;
        $this->dependent = $dependent;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        $same = ($this->base->getValue() == $this->dependent->getValue());

        return $same;
    }

}