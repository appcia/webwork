<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\Data\Value;
use Appcia\Webwork\View\Helper;

class Join extends Helper
{
    /**
     * Caller
     *
     * @param mixed  $data      Traversable data
     * @param string $separator Characters between values
     *
     * @return mixed
     */
    public function join($data, $separator = ', ')
    {
        if (Value::isArray($data)) {
            $values = array();

            foreach ($data as $value) {
                $value = Value::getString($value);

                if ($value !== null) {
                    $values[] = $value;
                }
            }

            $data = $values;
        } else {
            $data = array();
        }

        $result = implode($separator, $data);

        return $result;
    }
}
