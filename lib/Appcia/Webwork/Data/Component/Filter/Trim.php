<?

namespace Appcia\Webwork\Data\Component\Filter;

use Appcia\Webwork\Data\Component\Filter;

class Trim extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        if (!is_scalar($value)) {
            return $value;
        }

        $value = trim($value);

        return $value;
    }
}