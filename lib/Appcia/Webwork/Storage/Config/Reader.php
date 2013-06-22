<?

namespace Appcia\Webwork\Storage\Config;

use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\Storage\Config\Reader\Php;
use Appcia\Webwork\System\File;

abstract class Reader
{
    const PHP = 'php';

    /**
     * @var array
     */
    private static $types = array(
        self::PHP
    );

    /**
     * @return array
     */
    public static function getTypes()
    {
        return self::$types;
    }

    /**
     * Creator
     *
     * @param string|array $data Source (automatic determining) or reader config
     *
     * @throws \InvalidArgumentException
     * @return Reader
     */
    public static function create($data)
    {
        if (is_string($data)) {
            $data = array(
                'source' => $data
            );
        } elseif (!is_array($data)) {
            throw new \InvalidArgumentException("Reader data has invalid format.");
        }

        $config = new Config($data);
        $reader = null;

        if (isset($config['source'])) {
            $source = $config['source'];

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
                "Reader cannot be created. Invalid data specified."
            );
        }

        $config->inject($reader);

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