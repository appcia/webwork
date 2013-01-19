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
        if (!$date instanceof \DateTime) {
            $date = new \DateTime($date);
        }

        $now = new \DateTime('now');

        return $now->diff($date)->y;
    }
}
