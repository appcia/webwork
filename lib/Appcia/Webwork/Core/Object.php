<?

namespace Appcia\Webwork\Core;

use Appcia\Webwork\Storage\Config;

interface Object {
    /**
     * Object creator from various arguments
     *
     * @param mixed $data Config data
     * @param array $args Constructor arguments
     *
     * @return $this
     */
    public static function objectify($data, $args = array());
}

