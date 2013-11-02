<?

namespace Appcia\Webwork\Storage\Config;

use Appcia\Webwork\Core\Object;
use Appcia\Webwork\Core\Objector;
use Appcia\Webwork\Storage\Config\Reader\Php;
use Appcia\Webwork\System\File;

abstract class Reader implements Object
{
    /**
     * Creator
     *
     * @param mixed $data Source (automatic determining) or reader config
     * @param array $args Constructor arguments
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public static function objectify($data, $args = array())
    {
        if (is_string($data)) {
            $source = new File($data);
            $extension = $source->getExtension();

            switch ($extension) {
            case 'php':
            case 'php5':
                return new Php();
                break;
            }
        }

        return Objector::objectify($data, $args, get_called_class());
    }

    /**
     * Get config from source
     *
     * @param mixed $source Source
     *
     * @return $this
     */
    abstract public function read($source);
}