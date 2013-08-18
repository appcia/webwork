<?

namespace Appcia\Webwork\Data\Filter;

use Appcia\Webwork\Data\Filter;
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