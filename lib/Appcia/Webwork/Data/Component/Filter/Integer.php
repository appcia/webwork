<?

namespace Appcia\Webwork\Data\Component\Filter;

use Appcia\Webwork\Data\Component\Filter;
use Appcia\Webwork\Exception\Exception;

class Integer extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        if (!is_scalar($value)) {
            return $value;
        }

        $number = intval($value);

        return $number;
    }

}