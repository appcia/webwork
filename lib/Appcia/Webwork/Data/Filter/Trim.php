<?

namespace Appcia\Webwork\Data\Filter;

use Appcia\Webwork\Data\Filter;

class Trim extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        $result = trim($value);

        return $result;
    }
}