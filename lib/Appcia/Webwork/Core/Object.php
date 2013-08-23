<?

namespace Appcia\Webwork\Core;

use Appcia\Webwork\Storage\Config;

abstract class Object {

    /**
     * Creator
     *
     * @param mixed $data Config data
     * @param array $args Constructor arguments
     *
     * @return $this
     */
    public static function create($data, $args = array())
    {
        return Config::create($data, $args, get_called_class());
    }
}

