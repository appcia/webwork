<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Resource\Resource;
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
     * @param string $name   Resource name
     * @param array  $params Path parameters
     * @param mixed  $source Source file
     *
     * @return Resource|null
     */
    public function save($name, array $params, $source)
    {
        // Remove non-existing resource (hook for editing)
        if ($source instanceof Resource && !$source->exists()) {
            $source->remove();

            return null;
        }

        // Set static parameters
        $source = $this->retrieveFile($source);

        if (!isset($params['ext'])) {
            $params['ext'] = $source->getExtension();
        }

        $params['resource'] = $name;

        // Target location determining
        $resource = new Resource($this, $name, $params);
        $target = $resource->getFile();

        // Copy / download resource (only when it is required)
        if ($source->equals($target)) {
            return $resource;
        }

        $source->copy($target);

        // Run processing based on origin resource
        $resource->createTypes();

        return $resource;
    }

    /**
     * Remove resource and sub types
     *
     * @param string $name   Resource name
     * @param array  $params Path parameters
     *
     * @return $this
     */
    public function remove($name, array $params)
    {
        $resource = $this->load($name, $params);
        $this->removeFile($resource->getFile());

        foreach ($resource->getTypes() as $type) {
            $this->removeFile($type->getFile());
        }

        return $this;
    }

    /**
     * Get resource representation (could be non-existing)
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
     * Safely remove file and also directory if empty
     *
     * @param File|null $file File
     *
     * @return $this
     */
    protected function removeFile($file)
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
     * Retrieve file from various arguments
     *
     * @param mixed $resource
     *
     * @return File
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

        if ($file === null) {
            throw new \InvalidArgumentException(sprintf("Resource file does not exist."));
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
     * Upload resource to temporary path
     *
     * @param array $data   File data
     * @param array $params Path parameters
     *
     * @return Resource
     * @throws \InvalidArgumentException
     * @throws \ErrorException
     */
    public function upload($data, array $params)
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Invalid uploaded file data.');
        }

        $path = $data['name'];
        $sizeLimit = ini_get('upload_max_filesize');

        if ($data['error'] != UPLOAD_ERR_OK) {
            switch ($data['error']) {
            case UPLOAD_ERR_INI_SIZE:
                throw new \ErrorException(sprintf("Uploaded file '%s' size exceeds server limit: %d MB", $path, $sizeLimit));
                break;
            case UPLOAD_ERR_FORM_SIZE:
                throw new \ErrorException(sprintf("Uploaded file '%s' size exceeds form limit", $path));
                break;
            case UPLOAD_ERR_PARTIAL:
                throw new \ErrorException(sprintf("Uploaded file '%s' is only partially completed", $path));
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new \ErrorException("File has not been uploaded");
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                throw new \ErrorException(sprintf("Missing temporary directory for uploaded file: '%s'", $path));
                break;
            case UPLOAD_ERR_CANT_WRITE:
                throw new \ErrorException(sprintf("Failed to write uploaded file to disk: '%s'", $path));
                break;
            case UPLOAD_ERR_EXTENSION:
            default:
                throw new \ErrorException(sprintf("Unknown upload error: '%s'", $path));
                break;
            }
        }

        $source = new File($data['name']);
        $temp = new File($data['tmp_name']);

        if (!isset($params['ext'])) {
            $params['ext'] = $source->getExtension();
        }

        $resource = $this->save(
            Manager::UPLOAD,
            $params,
            $temp
        );

        return $resource;
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

    /**
     * Normalize uploaded file data
     *
     * @param array $data Data
     *
     * @return array|null
     * @throws \InvalidArgumentException
     */
    public function normalizeUpload($data)
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Uploaded data is not an array.' . PHP_EOL
            . 'Propably you just forget to add enctype multipart/form-data to form.');
        }

        // Trim empty values to null
        if (empty($data['tmp_name'])) {
            return null;
        }

        // Normalize for multiple files
        if (is_array($data['tmp_name'])) {
            $result = array();

            foreach ($data as $key => $all) {
                foreach ($all as $i => $val) {
                    $result[$i][$key] = $val;
                }
            }

            return $result;
        }

        return $data;
    }
}