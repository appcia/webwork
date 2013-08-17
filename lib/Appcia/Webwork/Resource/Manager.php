<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Resource\Service\Processor;
use Appcia\Webwork\Resource\Service\Provider;
use Appcia\Webwork\System\Dir;
use Appcia\Webwork\System\File;

/**
 * Resource manager with path mapping and subtype processing
 */
class Manager
{
    const UPLOAD = 'upload';

    /**
     * Resource map
     *
     * @var array
     */
    protected $resources;

    /**
     * @var Dir
     */
    protected $tempDir;

    /**
     * @var Processor[]
     */
    protected $processors;

    /**
     * @var Provider[]
     */
    protected $providers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->resources = array();
        $this->processors = array();
        $this->providers = array();
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
     * @return $this
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
     * @return $this
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

        $source = $this->retrieveFile($path);

        // Set static parameters
        $params['resource'] = $name;

        if (!isset($params['ext'])) {
            $params['ext'] = $source->getExtension();
        }

        $resource = new Resource($this, $name, $params);
        $target = $resource->getFile();

        // Copy / download resource (only when it is required)
        if ($source->equals($target)) {
            return $resource;
        }

        $source->copy($target);

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
     * @return $this
     */
    public function remove($name, array $params)
    {
        $resource = $this->load($name, $params);

        if ($resource !== null) {
            $this->removeFile($resource->getFile());

            foreach ($resource->getTypes() as $type) {
                $this->removeFile($type->getFile());
            }

        }

        return $this;
    }

    /**
     * Load resource
     *
     * @param string $name   Resource name
     * @param array  $params Path parameters
     *
     * @return Resource|null
     */
    public function load($name, array $params)
    {
        $resource = new Resource($this, $name, $params);
        $file = $resource->getFile();

        if ($file === null) {
            return null;
        }

        return $resource;
    }

    /**
     * Remove existing file and also directory if empty
     *
     * @param File|null $file File
     *
     * @return $this
     */
    protected function removeFile($file)
    {
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

    /**
     * Get processor for creating derivative types basing on original resource
     *
     * @param string $type   Type name
     * @param array  $config Configuration for type
     *
     * @return Processor
     * @throws \InvalidArgumentException
     * @throws \ErrorException
     */
    public function getProcessor($type, array $config)
    {
        if (empty($config['processor'])) {
            throw new \InvalidArgumentException(sprintf("Processor configuration for resource type '%s' not found", $type));
        }

        if (empty($config['processor']['class'])) {
            throw new \InvalidArgumentException(sprintf("Cannot find class name for resource type '%s'", $type));
        }

        $class = $config['processor']['class'];

        if (isset($this->processors[$class])) {
            return $this->processors[$class];
        }

        if (!class_exists($class)) {
            throw new \ErrorException(sprintf("Processor class '%s' does not exist", $class));
        }

        $processor = new $class();
        $processor->setManager($this);

        $this->processors[$class] = $processor;

        return $processor;
    }

    /**
     * @param $type
     *
     * @return Provider
     */
    public function getProvider($type)
    {
        // TODO Implementation
    }
}