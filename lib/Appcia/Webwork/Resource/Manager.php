<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Exception;
use Appcia\Webwork\Resource;
use Appcia\Webwork\System\Dir;
use Appcia\Webwork\System\File;
use Appcia\Webwork\Data\Form;

class Manager
{
    /**
     * Resource locations and other configurations
     *
     * @var array
     */
    private $map;

    /**
     * Directory for temporary files
     *
     * @var Dir
     */
    private $tempDir;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->map = array();
        $this->tempDir = new Dir(sys_get_temp_dir());
    }

    /**
     * @param array $map
     *
     * @return Manager
     */
    public function setMap(array $map)
    {
        $this->map = $map;

        return $this;
    }

    /**
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Set path for temporary files
     *
     * @param Dir|string $dir Dir object or path
     *
     * @return Manager
     */
    public function setTempDir($dir)
    {
        if (!$dir instanceof Dir) {
            $dir = new Dir($dir);
        }

        $this->tempDir = $dir;

        return $this;
    }

    /**
     * Get path for temporary files
     *
     * @return string
     */
    public function getTempDir()
    {
        return $this->tempDir;
    }

    /**
     * Get resource config from map
     *
     * @param string $resourceName Resource name
     *
     * @return mixed
     * @throws Exception
     */
    private function getConfig($resourceName)
    {
        if (!isset($this->map[$resourceName])) {
            throw new Exception('Invalid resource name');
        }

        $config = $this->map[$resourceName];

        if (!isset($config['path'])) {
            throw new Exception(sprintf("Path for resource '%s' not specified", $resourceName));
        }

        return $config;
    }

    /**
     * Inject data into path
     * Example format: '/public/news/{year}/{id}.{ext}'
     *
     * @param string $path   Path
     * @param array  $params Parameters
     *
     * @return mixed
     */
    private function parsePath($path, array $params)
    {
        if (empty($params)) {
            return $path;
        }

        foreach ($params as $key => $value) {
            $params['{' . $key . '}'] = $value;
            unset($params[$key]);
        }

        $path = str_replace(array_keys($params), array_values($params), $path);

        return $path;
    }

    /**
     * Process file to get more params available for naming
     *
     * @param Resource $resource Resource
     * @param array    $params   Path parameters
     *
     * @return array
     */
    private function processParams(Resource $resource, array $params)
    {
        $file = $resource->getFile();

        $params = array_merge($params, array(
            'name' => $file->getName(),
            'ext' => $file->getExtension()
        ));

        if ($file->exists()) {
            $params = array_merge($params, $file->getStat());
        }

        return $params;
    }

    /**
     * Load resource from source path
     *
     * @param string $name   Name
     * @param array  $params Path parameters
     *
     * @return Resource
     */
    public function load($name, array $params)
    {
        $config = $this->getConfig($name);
        $path = $this->parsePath($config['path'], $params);

        $resource = new Resource($path);

        if (!$resource->getFile()->exists()) {
            return null;
        }

        return $resource;
    }

    /**
     * Save resource in target path
     *
     * @param string   $resourceName Name
     * @param array    $params       Path parameters
     * @param Resource $source       External resource (could be temporary)
     *
     * @return Resource
     * @throws Exception
     */
    public function save($resourceName, array $params, $source)
    {
        if ($source === null) {
            throw new Exception('Invalid resource');
        }

        $file = $source->getFile();
        if (!$file->exists()) {
            throw new Exception(sprintf("Cannot save resource related with non-existing file: '%s'", $file->getPath()));
        }

        $params = $this->processParams($source, $params);
        $config = $this->getConfig($resourceName);
        $path = $this->parsePath($config['path'], $params);

        $target = new Resource($path);
        if ($source->isEqualTo($target)) {
            return $source;
        }

        $file->copy($path);

        if ($source->isTemporary()) {
            $file->remove();
        }

        return $target;
    }

    /**
     * Remove resource from filesystem
     *
     * @param string $resourceName Resource name
     * @param array  $params       Path parameters
     *
     * @return Manager
     */
    public function remove($resourceName, array $params)
    {
        $config = $this->getConfig($resourceName);
        $path = $this->parsePath($config['path'], $params);

        $resource = new Resource($path);
        $file = $resource->getFile();

        if ($file->exists()) {
            $file->remove();
        }

        return $this;
    }

    /**
     * Upload resource to temporary path
     *
     * @param string $token Token
     * @param string $key   Resource key
     * @param array  $data  File data
     *
     * @return Resource
     * @throws Exception
     */
    public function upload($token, $key, array $data)
    {
        if (empty($data)) {
            throw new Exception('Invalid file data');
        }

        $path = $data['name'];
        $sizeLimit = ini_get('upload_max_filesize');

        if ($data['error'] != UPLOAD_ERR_OK) {
            switch ($data['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    throw new Exception(sprintf("Uploaded file '%s' size exceeds server limit: %d MB", $path, $sizeLimit));
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    throw new Exception(sprintf("Uploaded file '%s' size exceeds form limit", $path));
                    break;
                case UPLOAD_ERR_PARTIAL:
                    throw new Exception(sprintf("Uploaded file '%s' is only partially completed", $path));
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new Exception("File has not been uploaded");
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    throw new Exception(sprintf("Missing temporary directory for uploaded file: '%s'", $path));
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    throw new Exception(sprintf("Failed to write uploaded file to disk: '%s'", $path));
                    break;
                case UPLOAD_ERR_EXTENSION:
                default:
                    throw new Exception(sprintf("Unknown upload error: '%s'", $path));
                    break;
            }
        }

        $sourceFile = new File($data['tmp_name']);

        $extension = pathinfo($data['name'], PATHINFO_EXTENSION);
        $suffix = $key . '_';

        $targetFile = $this->tempDir->generateRandomFile($extension, null, $suffix);
        $sourceFile->moveUploaded($targetFile);

        $resource = $this->createTemporary($path, $token);

        return $resource;
    }


    /**
     * Find resource by token
     *
     * @param string $token Token
     * @param string $key   Resource key
     *
     * @return Resource|null
     * @throws Exception
     */
    public function find($token, $key)
    {
        if (empty($token)) {
            throw new Exception('Token cannot be empty');
        }

        if (empty($key)) {
            throw new Exception('Resource key cannot be empty');
        }

        $pattern = $token . '_' . (string) $key . '.*';
        $paths = $this->tempDir->glob($pattern);
        $count = count($paths);

        if (empty($paths)) {
            return null;
        } elseif ($count > 1) {
            throw new Exception(sprintf("More than one resource (%d) matched token: '%s'", $count, $token));
        }

        $resource = $this->createTemporary($paths[0], $token);

        return $resource;
    }

    /**
     * Find all resources by token
     *
     * @param string $token Previously generated token
     *
     * @return array
     * @throws Exception
     */
    public function findAll($token)
    {
        if (empty($token)) {
            throw new Exception('Token cannot be empty');
        }

        $pattern = $token . '.*';
        $paths = $this->tempDir->glob($pattern);

        $resources = array();
        foreach ($paths as $path) {
            $resource = $this->createTemporary($path, $token);
            $resources[] = $resource;
        }

        return $resources;
    }

    /**
     * Create temporary resource from path
     *
     * @param string $token Token
     * @param string $path  Resource path
     *
     * @return Resource
     */
    private function createTemporary($path, $token)
    {
        $resource = new Resource($path);
        $resource->setTemporary(true);
        $resource->setToken($token);

        return $resource;
    }

}