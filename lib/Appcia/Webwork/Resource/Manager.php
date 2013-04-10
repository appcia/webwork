<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Exception;
use Appcia\Webwork\Resource;
use Appcia\Webwork\System\Dir;
use Appcia\Webwork\System\File;

class Manager
{
    /**
     * Resource map
     *
     * @var array
     */
    private $resources;

    /**
     * @var Dir
     */
    private $tempDir;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->resources = array();
    }

    /**
     * @param array $map
     */
    public function setResources($map)
    {
        $this->resources = $map;
    }

    /**
     * @return array
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Set directory for temporary files
     *
     * @param Dir|string $dir
     */
    public function setTempDir($dir)
    {
        if (!$dir instanceof Dir) {
            $dir = new Dir($dir);
        }

        $this->tempDir = $dir;
    }

    /**
     * Get directory for temporary files
     *
     * @return Dir
     */
    public function getTempDir()
    {
        return $this->tempDir;
    }

    /**
     * Save resource
     *
     * @param string $name
     * @param array  $params
     * @param string $path
     *
     * @return Resource|null
     */
    public function save($name, array $params, $path)
    {
        // Hook for removing when path is null
        if ($path === null) {
            $this->remove($name, $params);
            return null;
        }

        $sourceFile = $this->retrieveFile($path);

        // Set static parameters
        $params['resource'] = $name;

        if (!isset($params['ext'])) {
            $params['ext'] = $sourceFile->getExtension();
        }

        // Copy / download resource (only when it is required)
        $resource = new Resource($this, $name, $params);
        $targetFile = $resource->getFile(true);

        if (!$sourceFile->equals($targetFile)) {
            $sourceFile->copy($targetFile);
        }

        // Create subtypes (run processing based on source file)
        $resource->createTypes();

        return $resource;
    }

    /**
     * Load resource
     *
     * @param string $name   Resource name
     * @param array  $params Path parameters
     *
     * @return Resource
     */
    public function load($name, array $params)
    {
        $resource = new Resource($this, $name, $params);

        return $resource;
    }

    /**
     * Remove resource
     *
     * @param string $name   Resource name
     * @param array  $params Path parameters
     *
     * @return Manager
     */
    public function remove($name, array $params)
    {
        $resource = $this->load($name, $params);
        $file = $resource->getFile();

        if ($file !== null) {
            $file->remove();
        }

        return $this;
    }

    /**
     * Retrieve file object from various arguments
     *
     * @param Resource|File|string $resource
     *
     * @return File|null
     * @throws Exception
     */
    public function retrieveFile($resource)
    {
        $file = null;

        if (empty($resource)) {
            throw new Exception('Invalid resource provided');
        } else {
            if ($resource instanceof Type) {
                $file = $resource->getFile(true);
            } elseif ($resource instanceof File) {
                $file = $resource;
            } elseif (is_string($resource)) {
                $file = new File($resource);
            } else {
                throw new Exception('Invalid type of resource');
            }
        }

        return $file;
    }

    /**
     * Get resource configuration by name
     *
     * @param string $name
     *
     * @return mixed
     * @throws Exception
     */
    public function getConfig($name)
    {
        if (!isset($this->resources[$name])) {
            throw new Exception(sprintf("Configuration for resource '%s' not found", $name));
        }

        $config = $this->resources[$name];

        if (!isset($config['path'])) {
            throw new Exception(sprintf("Path for resource '%s' is not specified"));
        }

        return $config;
    }


}