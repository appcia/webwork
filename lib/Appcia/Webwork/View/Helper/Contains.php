<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Contains extends Helper
{
    /**
     * Caller
     *
     * @param mixed $value Value
     * @param array $set   Set
     *
     * @return bool
     */
    public function contains($value, array $set)
    {
        $contains = in_array($value, $set);

        return $contains;
    }
}
