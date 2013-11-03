<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\System\Dir;
use Appcia\Webwork\System\File;
use Appcia\Webwork\System\Php;
use Psr\Log\InvalidArgumentException;

/**
 * Resource manager with path mapping and subtype processing
 */
class Manager
{
    /**
     * Predefined types
     */
    const UPLOAD = 'upload';

    /**
     * Configuration map
     *
     * @var array
     */
    protected $config;

    /**
     * Temporary files directory
     *
     * @var Dir
     */
    protected $tempDir;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = array();
        $this->tempDir = new Dir(sys_get_temp_dir());
    }

    /**
     * @param Dir $dir
     *
     * @return $this
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
     * @return Dir
     */
    public function getTempDir()
    {
        return $this->tempDir;
    }

    /**
     * Get configuration (for type by names)
     *
     * @param string|null $resource
     * @param string|null $type
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getConfig($resource = null, $type = null)
    {
        $config = $this->config;

        if ($resource !== null) {
            $config = $config[$resource];

            if (!isset($config['path'])) {
                throw new \InvalidArgumentException(sprintf(
                    "Resource manager config for type '%s' does not have path specified.",
                    $resource
                ));
            }

            if (!isset($config['types'])) {
                $config['types'] = array();
            }
        }

        if ($type !== null) {
            $config = $config['types'];

            if (!isset($config[$type])) {
                throw new \InvalidArgumentException(sprintf(
                    "Resource manager config for type '%s' does not exist.",
                    $type
                ));
            }

            $config = $config[$type];

            if (!isset($config['path'])) {
                throw new \InvalidArgumentException(sprintf(
                    "Resource manager config for sub type '%s' does not have path specified.",
                    $type
                ));
            }
        }

        return $config;
    }

    /**
     * Set configuration
     *
     * @param array $config Config data
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = (array) $config;

        return $this;
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
     * Upload resource
     *
     * @param array $data    File data
     * @param array $params  Path parameters
     * @param bool  $useTemp Use temporary state
     *
     * @throws \InvalidArgumentException
     * @throws \ErrorException
     * @return Resource|null
     */
    public function upload($data, $params = array(), $useTemp = true)
    {
        if (empty($data)) {
            throw new \InvalidArgumentException("File data to be uploaded is empty.");
        }

        $resource = null;

        switch ($data['error']) {
        case UPLOAD_ERR_OK:
            $source = new File($data['name']);
            $temp = new File($data['tmp_name']);

            $params = array_merge(array(
                'filename' => $source->getFileName(),
                'extension' => $source->getExtension()
            ), $params);

            $resource = $this->map(static::UPLOAD, $params);
            $target = $resource->getFile();

            $temp->move($target);
            break;
        case UPLOAD_ERR_NO_FILE:
            if (!$useTemp) {
                throw new \ErrorException("File has not been uploaded");
            }
            break;
        case UPLOAD_ERR_INI_SIZE:
            throw new \ErrorException(sprintf(
                "Uploaded file size exceeds server limit: %d MB",
                Php::get('upload_max_filesize')
            ));
            break;
        case UPLOAD_ERR_FORM_SIZE:
            throw new \ErrorException("Uploaded file size exceeds form limit.");
            break;
        case UPLOAD_ERR_PARTIAL:
            throw new \ErrorException("Uploaded file is only partially completed.");
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            throw new \ErrorException("Missing temporary directory for uploaded file.");
            break;
        case UPLOAD_ERR_CANT_WRITE:
            throw new \ErrorException("Failed to write uploaded file to disk.");
            break;
        case UPLOAD_ERR_EXTENSION:
        default:
            throw new \ErrorException("Unknown upload error.");
            break;
        }

        return $resource;
    }

    /**
     * Get mapped resource
     *
     * @param string $name
     * @param array  $params
     *
     * @return Resource
     */
    public function map($name, $params = array())
    {
        $resource = new Resource($this, $name, $params);

        return $resource;
    }
}