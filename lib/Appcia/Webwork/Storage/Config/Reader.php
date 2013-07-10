<?

namespace Appcia\Webwork\Storage\Config;

use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\Storage\Config\Reader\Php;
use Appcia\Webwork\System\File;

abstract class Reader
{
    /**
     * Creator
     *
     * @param mixed $data Source (automatic determining) or reader config
     *
     * @throws \InvalidArgumentException
     * @return Reader
     */
    public static function create($data)
    {
        $reader = null;
        $type = null;
        $source = null;
        $config = null;

        if ($data instanceof Config) {
            $data = $data->getData();
        }

        if (is_string($data)) {
            $source = $data;
        } elseif (is_array($data)) {
            if (isset($data['type'])) {
                $type = (string) $data['type'];
            }

            $config = new Config($data);
        } else {
            throw new \InvalidArgumentException("Config reader data has invalid format.");
        }

        if ($type !== null) {
            $class = $type;
            if (!class_exists($class)) {
                $class =  __CLASS__ . '\\' . ucfirst($type);
            }

            if (class_exists($class) && !is_subclass_of($class, __CLASS__)) {
                throw new \InvalidArgumentException(sprintf("Config reader '%s' is invalid or unsupported.", $type));
            }

            $reader = new $class();
        }

        if ($reader === null && $source !== null) {
            $source = new File($source);
            $extension = $source->getExtension();

            switch ($extension) {
                case 'php':
                case 'php5':
                    $reader = new Php();
                    break;
            }
        }

        if ($reader === null) {
            throw new \InvalidArgumentException(
                "Config reader cannot be created. Invalid data specified."
            );
        }

        if ($config !== null) {
            $config->inject($reader);
        }

        return $reader;
    }

    /**
     * Get config from source
     *
     * @param mixed $source Source
     *
     * @return Config
     */
    abstract public function read($source);
}