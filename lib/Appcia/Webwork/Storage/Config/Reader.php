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
     * @return $this
     */
    public static function create($data)
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

        return Config::create($data, get_called_class());
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