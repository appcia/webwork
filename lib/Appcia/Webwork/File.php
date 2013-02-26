<?

namespace Appcia\Webwork;

class File {

    private $dir;

    private $tempDir;

    private $name;

    public function __construct($path) {
        $info = pathinfo($path);

        if (empty($info)) {
            throw new \InvalidArgumentException('Invalid file path');
        }

        list ($this->dir, $this->path, $this->extension) =
        $this->tempDir = $this->dir . '/tmp';

        if ($this->name === '*') {
            if (!is_dir($this->dir) && !@mkdir($this->tmpPath, 0644, true)) {
                throw new \ErrorException(sprintf("Cannot make temporary directory: %s", $this->tmpPath));
            }

            $this->name = tempnam($this->tempDir, '');
        }
    }

    public function getDir()
    {
        return $this->dir;
    }

    public function getTempDir()
    {
        return $this->tempDir;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath()
    {
        return $this->dir . '/' . $this->name;
    }
}