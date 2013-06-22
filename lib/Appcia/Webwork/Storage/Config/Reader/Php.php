<?

namespace Appcia\Webwork\Storage\Config\Reader;

use Appcia\Webwork\Storage\Config\Reader;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\System\File;

class Php extends Reader
{
    /**
     * @param string $source
     *
     * @throws \LogicException
     * @throws \ErrorException
     *
     * @return Config
     */
    public function read($source)
    {
        $file = new File($source);

        if (!$file->exists()) {
            throw new \LogicException(sprintf("Config source file '%s' does not exist.", $source));
        }

        // Possible syntax errors, do not handle them / expensive!
        $path = $file->getPath();
        $data = include $path;

        if (!is_array($data)) {
            throw new \ErrorException("Config source file should return array: '%s'", $source);
        }

        $config = new Config();
        $config->setData($data);

        return $config;
    }
}