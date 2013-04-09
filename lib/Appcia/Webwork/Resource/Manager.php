<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Data\Form;
use Appcia\Webwork\Exception;
use Appcia\Webwork\Resource;
use Appcia\Webwork\System\Dir;
use Appcia\Webwork\System\File;

class Manager
{
    const TEMPORARY = self::TEMPORARY;
    
    /**
     * Resource map: locations and other configurations
     *
     * @var array
     */
    private $map;

    /**
     * @var array
     */
    private $plugins;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->map = array();
        $this->plugins = array();
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
     * Get specific resource configuration
     *
     * @param string $resourceName Resource name
     *
     * @return mixed
     * @throws Exception
     */
    public function getConfig($resourceName)
    {
        if (!isset($this->map[$resourceName])) {
            throw new Exception(sprintf("Invalid resource name '%s'", $resourceName));
        }

        $config = $this->map[$resourceName];

        if (!isset($config['path'])) {
            throw new Exception(sprintf("Path for resource '%s' not specified", $resourceName));
        }

        return $config;
    }

    /**
     * Get resource file
     *
     * @param Manager $manager      Origin factory
     * @param string  $resourceName Resource type
     * @param array   $params       Path parameters
     *
     * @return Resource
     */
    public function load($resourceName, array $params)
    {
        $resource = new Resource($this, $resourceName, $params);
        
        $file = $resource->getFile();
        if ($file === null) {
            throw new Exception(sprintf("Cannot determine resource file for name '%s'", $resourceName));
        }

        return $resource;
    }

    /**
     * Save file as resource
     *
     * @param string $resourceName Resource name
     * @param array  $params       Path parameters
     * @param File   $file         File to be saved
     *
     * @return Resource
     * @throws Exception
     */
    public function save($resourceName, array $params, $file)
    {
        if (!$file instanceof File) {
            $file = new File($file);
        }

        if (!$file->exists()) {
            throw new Exception(sprintf("Cannot save resource related with non-existing file: '%s'", $file->getPath()));
        }

        $resource = new Resource($this, $resourceName, $params);
        $targetFile = $resource->getFile();
        if ($targetFile === null) {
            throw new Exception(sprintf("Cannot determine resource target file for name: '%s", $resourceName));
        }

        // Copy source file to target resource path
        $file->copy($targetFile);

        // Remove source temporary file
        if ($resourceName === self::TEMPORARY) {
            $file->remove();
        }

        // Create resource subtypes
        $resource->createTypes();

        return $resource;
    }

    /**
     * Remove resource from filesystem
     *
     * @param string $type   Resource type
     * @param array  $params Path parameters
     *
     * @return Manager
     */
    public function remove($type, array $params)
    {
        $resource = $this->load($type, $params);
        $file = $resource->getFile();

        if ($file !== null && $file->exists()) {
            $file->remove();
        }

        return $this;
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
        $extension = $sourceFile->getExtension();
        $suffix = '_' . (string) $key;
        $targetFile = new File($this->tempDir->getPath($token . $suffix . '.' . $extension));

        $tempFile = new File($data['tmp_name']);
        $tempFile->moveUploaded($targetFile);

        $resource = $this->save(
            self::TEMPORARY,
            array(
                'token' => $token,
                'key' => $key
            ),
            $tempFile
        );

        return $resource;
    }
}