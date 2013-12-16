<?

namespace Appcia\Webwork\Data\Component\Validator;

use Appcia\Webwork\Data\Arr;
use Appcia\Webwork\Data\Component\Validator;
use Appcia\Webwork\Data\Value;

class Contains extends Validator
{

    /**
     * Possible values
     *
     * @var array
     */
    protected $values;

    /**
     * Constructor
     */
    public function __construct($values)
    {
        $this->values = $values;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if (Value::isEmpty($value)) {
            return true;
        }
        $valid = Arr::contains($value, $this->values);

        return $valid;
    }
}