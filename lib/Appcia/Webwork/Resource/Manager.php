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
     * @var Dir
     */
    protected $tempDir;

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
            throw new \InvalidArgumentException("File to be uploaded is empty.");
        }

        $source = new File($data['name']);
        $temp = new File($data['tmp_name']);

        $params = array_merge(array(
            'filename' => $source->getFileName(),
            'extension' => $source->getExtension()
        ), $params);

        $resource = $this->map(static::UPLOAD, $params);
        $target = $resource->getFile();
        $path = $source->getPath();

        switch ($data['error']) {
        case UPLOAD_ERR_OK:
            $temp->move($target);
            break;
        case UPLOAD_ERR_NO_FILE:
            if (!$useTemp) {
                throw new \ErrorException("File has not been uploaded");
            }
            break;
        case UPLOAD_ERR_INI_SIZE:
            throw new \ErrorException(sprintf(
                "Uploaded file '%s' size exceeds server limit: %d MB",
                $path,
                Php::get('upload_max_filesize')
            ));
            break;
        case UPLOAD_ERR_FORM_SIZE:
            throw new \ErrorException(sprintf("Uploaded file '%s' size exceeds form limit", $path));
            break;
        case UPLOAD_ERR_PARTIAL:
            throw new \ErrorException(sprintf("Uploaded file '%s' is only partially completed", $path));
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

    /**
     * @param string|null $resource
     * @param string|null $type
     *
     * @return array
     */
    public function getConfig($resource = null, $type = null)
    {
        $config = $this->config;

        if ($resource !== null) {
            $config = $config[$resource];
        }

        if ($type !== null) {
            $config = isset($config['types'])
                ? $config['types']
                : array();

            $config = $config[$type];
        }

        return $config;
    }

    /**
     * @param array $config
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