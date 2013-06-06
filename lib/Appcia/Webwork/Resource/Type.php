<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\System\File;

/**
 * Subtype of resource (thumbnail, format derivation)
 *
 * @package Appcia\Webwork\Resource
 */
class Type
{
    const EXTENSION_WILDCARD = '*';

    /**
     * Base resource
     *
     * @var Resource
     */
    private $resource;

    /**
     * Path pattern
     *
     * @var string
     */
    private $path;

    /**
     * Parameters for path generation
     *
     * @var array
     */
    private $params;

    /**
     * Lazy located file
     *
     * @var File
     */
    private $file;

    /**
     * Constructor
     *
     * @param Resource $resource Resource
     * @param string   $path     Path
     * @param array    $params   Parameters for path
     */
    public function __construct(Resource $resource, $path, array $params = array())
    {
        $this->resource = $resource;
        $this->path = $path;
        $this->params = $params;
    }

    /**
     * Get base resource
     *
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Get path pattern
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get parameters for path generation
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get lazy loaded file
     *
     * @return $this|null
     * @throws \ErrorException
     */
    public function getFile()
    {
        if ($this->file === null) {
            $file = $this->determineFile($this->path, $this->params);

            if ($file !== null) {
                $this->params['ext'] = $file->getExtension();
                $this->file = $file;
            }
        }

        return $this->file;
    }

    /**
     * Locate file on filesystem using path and parameters
     * If file extension equals wildcard '*' then it will be guessed using glob
     *
     * @param string $path   Path pattern
     * @param array  $params Parameters for path
     *
     * @return $this|null
     */
    private function determineFile($path, array $params)
    {
        // Extension usually is unknown so use wildcard (except case when saving resource)
        if (!isset($params['ext'])) {
            $params['ext'] = self::EXTENSION_WILDCARD;
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

        if ($file->getExtension() === self::EXTENSION_WILDCARD) {
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
     * Returns file string representation
     *
     * @return string
     */
    public function __toString()
    {
        $file = $this->getFile();

        if ($file === null) {
            return '';
        } else {
            return (string) $file;
        }
    }

}