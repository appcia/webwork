<?

namespace Appcia\Webwork\Storage\Config;

use Appcia\Webwork\Core\Object;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\Storage\Config\Writer\Php;
use Appcia\Webwork\System\File;

abstract class Writer extends Object
{
    /**
     * Creator
     *
     * @param mixed $data Target (automatic determining) or writer config
     * @param array $args Constructor arguments
     *
     * @return Writer
     * @throws \InvalidArgumentException
     */
    public static function create($data, $args = array())
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

        return parent::create($data, $args);
    }

    /**
     * Save config to target
     *
     * @param Config $config Configuration
     * @param mixed  $target Target
     *
     * @return void
     */
    abstract public function write(Config $config, $target);
}