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
        $data = $this->getStringValue($data);
        if ($data === null) {
            return null;
        }

        $data = trim($data);

        return $data;
    }
}
