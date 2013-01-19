<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator;
use Appcia\Webwork\Data\Form\Field;

class Same extends Validator
{
    /**
     * First dependent
     */
    private $field1;

    /**
     * Second dependent
     */
    private $field2;

    /**
     * Constructor
     *
     * @param Field $f1
     * @param Field $f2
     */
    public function __construct(Field $f1, Field $f2)
    {
        $this->field1 = $f1;
        $this->field2 = $f2;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($data)
    {
        return $this->field1->getValue == $this->field2->getValue();
    }

}