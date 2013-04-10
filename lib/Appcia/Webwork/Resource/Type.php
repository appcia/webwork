<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Exception;
use Appcia\Webwork\System\File;

class Type
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $params;

    /**
     * @var File
     */
    private $file;

    /**
     * @param string $path
     * @param array  $params
     */
    public function __construct($path, array $params = array())
    {
        $this->path = $path;
        $this->params = $params;
        $this->file = null;
    }
    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param bool $force Throw exception when cannot be done
     *
     * @return File|null
     * @throws Exception
     */
    public function getFile($force = false)
    {
        if ($this->file === null) {
            $file = $this->determineFile($this->path, $this->params);

            if ($file !== null) {
                $this->params['ext'] = $file->getExtension();
                $this->file = $file;
            } else if ($force) {
                throw new Exception(sprintf("Cannot determine target file for resource '%s'", $this->name));
            }
        }

        return $this->file;
    }

    /**
     * @param string $path
     * @param array  $params
     *
     * @return File|null
     */
    private function determineFile($path, array $params)
    {
        // Extension usually is unknown so use wildcard (except case when saving resource)
        if (!isset($params['ext'])) {
            $params['ext'] = '*';
        }

        foreach ($params as $key => $value) {
            $params['{' . $key . '}'] = $value;
            unset($params[$key]);
        }

        $path = str_replace(
            array_keys($params),
            array_values($params),
            $path
        );

        // Use glob to know extension
        $file = new File($path);

        if ($file->getExtension() === '*') {
            $dir = $file->getDir();
            $paths = $dir->glob($file->getBaseName());
            $count = count($paths);

            // Only when exactly one match file name
            if ($count === 1) {
                $file->setPath($paths[0]);
            } else {
                return null;
            }
        }

        return $file;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $file = $this->getFile();

        if ($file === null) {
            return '';
        } else {
            return $file->__toString();
        }
    }

}