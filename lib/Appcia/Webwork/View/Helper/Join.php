<?

namespace Appcia\Webwork\View\Helper;

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
        if ($data instanceof \Traversable) {
            $values = array();

            foreach ($data as $value) {
                $value = $this->getStringValue($value);

                if ($value !== null) {
                    $values[] = $value;
                }
            }

            $data = $values;
        } elseif (!is_array($data)) {
            $data = array();
        }

        $result = implode($separator, $data);

        return $result;
    }
}
