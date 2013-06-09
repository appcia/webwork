<?

namespace Appcia\Webwork\Data\Form\Field;

use Appcia\Webwork\Data\Form\Field;

/**
 * Field with set of values (arrays)
 *
 * @package Appcia\Webwork\Data\Form\Field
 */
class Set extends Field
{
    /**
     * {@inheritdoc}
     */
    public function filter()
    {
        if (is_array($this->value)) {
            foreach ($this->filters as $filter) {
                foreach ($this->value as $key => $value) {
                    $this->value[$key] = $filter->filter($value);
                }
            }
        }

        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        $this->valid = true;

        if (!is_array($this->value)) {
            $this->valid = false;
        } else {
            foreach ($this->validators as $validator) {
                $valid = true;

                foreach ($this->value as $value) {
                    if (!$validator->validate($value)) {
                        $valid = false;
                        break;
                    }
                }

                if (!$valid) {
                    $this->valid = false;
                    break;
                }
            }
        }

        return $this->valid;
    }
}