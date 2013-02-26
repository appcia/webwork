<?

namespace Appcia\Webwork\File;

use Appcia\Webwork\File;

class Manager
{
    private $tempDir;

    public function __construct(array $config) {
        if (!isset($config['tempDir'])) {
            throw new \InvalidArgumentException('Temporary directory is not specified');
        }

        $this->tempDir = $config['tempDir'];
    }


}