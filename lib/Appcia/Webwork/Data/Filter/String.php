<?

namespace Appcia\Webwork\Data\Filter;

use Appcia\Webwork\Data\Filter;
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