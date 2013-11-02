<?

namespace Appcia\Webwork\Storage\Config;

use Appcia\Webwork\Core\Object;
use Appcia\Webwork\Core\Objector;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\Storage\Config\Writer\Php;
use Appcia\Webwork\System\File;

abstract class Writer implements Object
{
    /**
     * Creator
     *
     * @param mixed $data Target (automatic determining) or writer config
     * @param array $args Constructor arguments
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public static function objectify($data, $args = array())
    {
        if (is_string($data)) {
            $target = new File($data);
            $extension = $target->getExtension();

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
     * Save config to target
     *
     * @param Config $config Configuration
     * @param mixed  $target Target
     *
     * @return $this
     */
    abstract public function write(Config $config, $target);
}