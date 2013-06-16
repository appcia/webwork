<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Age extends Helper
{
    /**
     * Caller
     *
     * @param mixed $date Unix timestamp or string, e.g '+ 1 week'
     *
     * @return string
     */
    public function age($date)
    {
        if ($this->isEmptyValue($date)) {
            return null;
        }

        $date = $this->getDateValue($date);
        $now = new \DateTime('now');

        $age = $now->diff($date)->y;

        return $age;
    }
}
