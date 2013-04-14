<?

namespace Appcia\Webwork\Data;

use Appcia\Webwork\Exception;

class Serializer {

    /**
     * Safely serialize data
     *
     * @param array $data Data
     *
     * @return string
     */
    public function serialize(array $data)
    {
        $data = @serialize($data);
        if ($data === false) {
            throw new Exception('Cannot serialize value');
        }

        $data = @base64_encode($data);
        if ($data === false) {
            throw new Exception('Cannot encode serialized value');
        }

        return $data;
    }

    /**
     * Safely unserialize value
     *
     * @param string $value Value
     *
     * @return array
     * @throws Exception
     */
    public function unserialize($value)
    {
        if (empty($value)) {
            return array();
        }

        $data = @base64_decode($value);
        if ($data === false) {
            throw new Exception('Cannot decode serialized value');
        }

        $data = @unserialize($data);
        if ($data === false) {
            throw new Exception('Cannot unserialize value');
        }

        if (!is_array($data)) {
            throw new Exception('Unserialize error. Data suppose to be an array');
        }

        return $data;
    }
}