<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\System\Dir;
use Appcia\Webwork\System\File;

/**
 * Resource manager with path mapping and subtype processing
 *
 * @package Appcia\Webwork\Resource
 */
class Manager
{
    const UPLOAD = 'upload';

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
     * Get resources
     *
     * @return array
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Get resources
     *
     * @param array $map Config map
     *
     * @return Manager
     */
    public function setResources($map)
    {
        $this->resources = $map;

        return $this;
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
     * Set directory for temporary files
     *
     * @param Dir|string $dir Path
     *
     * @return Manager
     */
    public function setTempDir($dir)
    {
        if (!$dir instanceof Dir) {
            $dir = new Dir($dir);
        }

        if (!$dir->exists()) {
            $dir->create();
        }

        $this->tempDir = $dir;

        return $this;
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

        $resource = new Resource($this, $name, $params);
        $targetFile = $resource->getFile();

        // Copy / download resource (only when it is required)
        if ($sourceFile->equals($targetFile)) {
            return $resource;
        }

        $sourceFile->copy($targetFile);

        // Run processing based on origin resource)
        $resource->createTypes();

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

        $this->removeFile($resource->getFile(false));
        foreach ($resource->getTypes() as $type) {
            $this->removeFile($type->getFile(false));
        }

        return $this;
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
     * Remove existing file and also directory if empty
     *
     * @param File|null $file File
     *
     * @return $this
     */
    private function removeFile($file)
    {
        if ($file === null) {
            return $this;
        }

        if ($file->exists()) {
            $file->remove();
        }

        $dir = $file->getDir();
        if ($dir->isEmpty()) {
            $dir->remove();
        }

        return $this;
    }

    /**
     * Retrieve file object from various arguments
     *
     * @param Resource|File|string $resource
     *
     * @return File|null
     * @throws \InvalidArgumentException
     */
    public function retrieveFile($resource)
    {
        $file = null;

        if (empty($resource)) {
            throw new \InvalidArgumentException('Resource file is not specified.');
        } else {
            if ($resource instanceof Type) {
                $file = $resource->getFile();
            } elseif ($resource instanceof File) {
                $file = $resource;
            } elseif (is_string($resource)) {
                $file = new File($resource);
            } else {
                throw new \InvalidArgumentException('Resource has unsupported type.');
            }
        }

        return $file;
    }

    /**
     * Get resource configuration by name
     *
     * @param string $name Name
     *
     * @return mixed
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    public function getConfig($name)
    {
        if (!isset($this->resources[$name])) {
            throw new \OutOfBoundsException(sprintf("Resource '%s' configuration not found.", $name));
        }

        $config = $this->resources[$name];

        if (!isset($config['path'])) {
            throw new \InvalidArgumentException(sprintf("Resource '%s' path is not specified"));
        }

        return $config;
    }


}