<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Exception;
use Appcia\Webwork\Resource;
use Appcia\Webwork\Session;

class Manager
{
    /**
     * @var array
     */
    private $map;

    /**
     * @var
     */
    private $temporaryPath;

    /**
     * Constructor
     *
     * @todo Remove session dependency, only use tokens passed from controller
     */
    public function __construct(Session $session, $namespace = 'resourceManager')
    {
        $this->session = $session;
        $this->namespace = $namespace;
        $this->tokens = array();

        $this->map = array();
        $this->temporaryPath = sys_get_temp_dir();

        $this->loadTokens();
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
     * Get path for temporary files
     *
     * @param string $temporaryPath Path
     *
     * @return Manager
     */
    public function setTemporaryPath($temporaryPath)
    {
        $this->temporaryPath = $temporaryPath;

        return $this;
    }

    /**
     * Set path for temporary files
     *
     * @return string
     */
    public function getTemporaryPath()
    {
        return $this->temporaryPath;
    }

    /**
     * Load tokens from session
     *
     * @return Manager
     */
    private function loadTokens()
    {
        if ($this->session->has($this->namespace)) {
            $this->tokens = $this->session->get($this->namespace);
        }

        return $this;
    }

    /**
     * Save tokens in session
     *
     * @return Manager
     */
    private function saveTokens()
    {
        $this->session->set($this->namespace, $this->tokens);

        return $this;
    }

    /**
     * Get resource config from map
     *
     * @param string $resourceName
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
     * Format: '/public/news/{year}/{id}.{ext}'
     *
     * @param string $path
     * @param array $params
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
     * Get temporary file name with correct extension
     *
     * @param string $origin Origin file path
     * @return string
     */
    private function getTemporaryFile($origin)
    {
        $temp = null;
        $ext = pathinfo($origin, PATHINFO_EXTENSION);

        do {
            $temp = $this->temporaryPath . '/' . uniqid('', true) . '.' . $ext;
        } while (file_exists($temp));

        return $temp;
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
     * @param $resourceName
     * @param array $params
     * @return $this
     */
    public function remove($resourceName, array $params)
    {
        //@todo Complete removing resource

        return $this;
    }

    /**
     * @param $token
     * @return Resource|null
     */
    public function find($token)
    {
        if (!isset($this->tokens[$token])) {
            return null;
        }

        $path = $this->tokens[$token];

        $resource = new Resource($path);
        $resource->setTemporary(true);

        if (!$resource->getFile()->exists()) {
            return null;
        }

        return $resource;
    }

    /**
     * Upload resource to temporary path
     *
     * @param array $data
     * @param string $token
     *
     * @return Resource
     * @throws Exception
     */
    public function upload(array $data, $token)
    {
        if (empty($data)) {
            throw new Exception('Invalid file data');
        }

        $file = $data['name'];
        $sizeLimit = ini_get('upload_max_filesize');

        if ($data['error'] != UPLOAD_ERR_OK) {
            switch ($data['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    throw new Exception(sprintf("Uploaded file '%s' size exceeds server limit: %d MB", $file, $sizeLimit));
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    throw new Exception(sprintf("Uploaded file '%s' size exceeds form limit", $file));
                    break;
                case UPLOAD_ERR_PARTIAL:
                    throw new Exception(sprintf("Uploaded file '%s' is only partially completed", $file));
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new Exception("File has not been uploaded");
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    throw new Exception(sprintf("Missing temporary directory for uploaded file: '%s'", $file));
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    throw new Exception(sprintf("Failed to write uploaded file to disk: '%s'", $file));
                    break;
                case UPLOAD_ERR_EXTENSION:
                default:
                    throw new Exception(sprintf("Unknown upload error: '%s'", $file));
                    break;
            }
        }

        $sourcePath = $data['tmp_name'];
        $targetPath = $this->getTemporaryFile($data['name']);

        if (!is_writable($this->temporaryPath)) {
            throw new Exception(sprintf("Temporary path is not writeable: '%s'", $this->temporaryPath));
        }

        if (!move_uploaded_file($sourcePath, $targetPath)) {
            throw new Exception(sprintf("Cannot move uploaded file: %s -> %s", $sourcePath, $targetPath));
        }

        $file = $this->temporaryPath . '/' . basename($targetPath);

        $resource = new Resource($file);
        $resource->setTemporary(true);

        $this->tokens[$token] = $resource->getFile()->getPath();
        $this->saveTokens();

        return $resource;
    }

}