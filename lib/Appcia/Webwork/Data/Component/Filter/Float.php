<?

namespace Appcia\Webwork\Data\Component\Filter;

use Appcia\Webwork\Data\Component\Filter;

class Float extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        if (!is_scalar($value)) {
            return $value;
        }

        $number = floatval($value);

        return $number;
    }

}