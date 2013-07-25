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
        $writer = null;
        $type = null;
        $target = null;
        $config = null;
        
        if ($data instanceof Config) {
            $data = $data->getData();
        }
        
        if (is_string($data)) {
            $target = $data;
        } elseif (is_array($data)) {
            if (isset($data['type'])) {
                $type = (string) $data['type'];
            }

            $config = new Config($data);
        } else {
            throw new \InvalidArgumentException("Config writer data has invalid format.");
        }


        if ($type !== null) {
            $class = $type;
            if (!class_exists($class)) {
                $class =  __CLASS__ . '\\' . ucfirst($type);
            }

            if (class_exists($class) && !is_subclass_of($class, __CLASS__)) {
                throw new \InvalidArgumentException(sprintf("Config writer '%s' is invalid or unsupported.", $type));
            }

            $writer = new $class();
        }

        if ($writer === null && $target !== null) {
            $target = new File($target);
            $extension = $target->getExtension();

            switch ($extension) {
                case 'php':
                case 'php5':
                    $writer = new Php();
                    break;
            }
        }

        if ($writer === null) {
            throw new \InvalidArgumentException(
                "Config writer cannot be created. Invalid data specified."
            );
        }

        if ($config !== NULL) {
            $config->inject($writer);
        }

        return $writer;
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