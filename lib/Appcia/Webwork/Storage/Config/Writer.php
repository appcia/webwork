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
     * @param string|array $data Target (automatic determining) or writer config
     *
     * @return Writer
     * @throws \InvalidArgumentException
     */
    public static function create($data)
    {
        if (is_string($data)) {
            $data = array(
                'target' => $data
            );
        } elseif (!is_array($data)) {
            throw new \InvalidArgumentException("Writer data has invalid format.");
        }

        $config = new Config($data);
        $writer = null;

        if (isset($config['target'])) {
            $target = $config['target'];

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
                "Writer cannot be created. Invalid data specified."
            );
        }

        $config->inject($writer);

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