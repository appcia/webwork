<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Data\Form;
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
    private $map;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->map = array();
    }

    /**
     * @param array $map
     */
    public function setMap($map)
    {
        $this->map = $map;
    }

    /**
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
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

        // Add extension as param if not specified
        if (!isset($params['ext'])) {
            $params['ext'] = $sourceFile->getExtension();
        }

        $resource = new Resource($this, $name, $params);
        $targetFile = $resource->getFile(true);

        // Copy only when it is required
        if (!$sourceFile->equals($targetFile)) {
            $sourceFile->copy($targetFile);
        }

        return $resource;
    }

    public function load($name, array $params)
    {
        $resource = new Resource($this, $name, $params);

        return $resource;
    }

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
     * Upload resource to temporary path
     *
     * @param string $token Token
     * @param string $key   Resource key
     * @param array  $data  File data
     *
     * @return Resource
     * @throws Exception
     */
    public function upload($token, $key, $data)
    {
        if (empty($data)) {
            throw new Exception('Invalid uploaded file data');
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

        $sourceFile = new File($data['name']);
        $tempFile = new File($data['tmp_name']);

        $resource = $this->save(
            'temporary',
            array(
                'token' => $token,
                'key' => $key,
                'ext' => $sourceFile->getExtension()
            ),
            $tempFile
        );

        return $resource;
    }

    /**
     * Normalize uploaded file data
     *
     * @param array $data Data
     *
     * @return array|null
     */
    public function normalizeUpload(array $data)
    {
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

    /**
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
            if ($resource instanceof Resource) {
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
    private function getConfig($name)
    {
        if (!isset($this->map[$name])) {
            throw new Exception(sprintf("Configuration for resource '%s' not found", $name));
        }

        $config = $this->map[$name];

        if (!isset($config['path'])) {
            throw new Exception(sprintf("Path for resource '%s' is not specified"));
        }

        return $config;
    }

    /**
     * @param string $name
     * @param array  $params
     *
     * @return File|null
     */
    public function determineFile($name, array $params)
    {
        // Inject params into path from configuration
        $config = $this->getConfig($name);

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
            $config['path']
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

}