<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Trim extends Helper
{
    /**
     * Caller
     *
     * @param mixed $data Data
     *
     * @return mixed
     */
    public function trim($data)
    {
        if (!is_scalar($data)) {
            return null;
        }

        $data = trim($data);

        return $data;
    }
}
