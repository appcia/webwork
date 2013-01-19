<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Join extends Helper
{
    /**
     * Caller
     *
     * @param mixed  $data
     * @param string $key
     * @param string $separator
     * @return mixed
     */
    public function join($data, $key = null, $separator = ' ')
    {
        if (!$data instanceof \Traversable) {
            return null;
        }

        if ($key !== null) {
            $values = array();
            foreach ($data as $value) {
                if (is_array($value) && isset($value[$key])) {
                    $values[] = $value[$key];
                    continue;
                }

                $callback = array($value, 'get' . ucfirst($key));

                if (is_callable($callback)) {
                    $values[] = call_user_func($callback);
                    continue;
                }
            }

            $data = $values;
        }

        return implode($separator, $data);
    }
}
