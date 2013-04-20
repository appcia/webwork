<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Join extends Helper
{
    /**
     * Caller
     *
     * @param mixed  $data      Traversable data (can be iterated by foreach loop)
     * @param string $property  Object property name to be retrieved
     * @param string $separator Characters between values
     *
     * @return mixed
     */
    public function join($data, $property = null, $separator = ', ')
    {
        if (!$data instanceof \Traversable) {
            return null;
        }

        if ($property !== null) {
            $values = array();
            foreach ($data as $value) {
                if (is_array($value) && isset($value[$property])) {
                    $values[] = $value[$property];
                    continue;
                }

                $callback = array($value, 'get' . ucfirst($property));

                if (is_callable($callback)) {
                    $values[] = call_user_func($callback);
                    continue;
                }
            }

            $data = $values;
        }

        $list = implode($separator, $data);

        return $list;
    }
}
