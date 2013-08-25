<?

namespace Appcia\Webwork\Data\Component\Filter;

use Appcia\Webwork\Data\Component\Filter;
use Appcia\Webwork\Data\Value;

class String extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        $value = Value::getString($value);

        return $value;
    }

}