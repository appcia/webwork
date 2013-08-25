<?

namespace Appcia\Webwork\Data\Component\Filter;

use Appcia\Webwork\Data\Component\Filter;
use Appcia\Webwork\Data\Value;

class Date extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        $value = Value::getDate($value);

        return $value;
    }
}