<?

namespace Appcia\Webwork\Storage\Config;

use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\Storage\Config\Writer\Php;
use Appcia\Webwork\System\File;

abstract class Writer
{
    /**
     * Creator
     *
     * @param mixed $data Target (automatic determining) or writer config
     *
     * @return Writer
     * @throws \InvalidArgumentException
     */
    public static function create($data)
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

        return Config::create($data, __CLASS__);
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