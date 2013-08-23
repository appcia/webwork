<?

namespace Appcia\Webwork\Storage\Config\Writer;

use Appcia\Webwork\Storage\Config\Writer;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\System\File;

class Php extends Writer
{
    /**
     * {@inheritdoc}
     */
    public function write(Config $config, $target)
    {
        $file = new File($target);

        if ($file->exists()) {
            throw new \LogicException(sprintf("Config target file already exists '%s'", $target));
        }

        $data = sprintf("<?php\n\n// \nreturn %s;\n", var_export($config->getData(), true));
        $file->write($data);

        return $this;
    }
}