<?

namespace Appcia\Webwork\Data\Component\Validator;

use Appcia\Webwork\Data\Component\Validator;

class Not extends Validator
{
    /**
     * Wrapped validator
     *
     * @var Validator
     */
    protected $validator;

    /**
     * Constructor
     *
     * @param Validator $validator Validator for result negation
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        $flag = !$this->validator->validate($value);

        return $flag;
    }
}