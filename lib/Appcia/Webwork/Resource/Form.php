<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Data\Form as BasicForm;
use Appcia\Webwork\Data\Form\Field;
use Appcia\Webwork\Exception;
use Appcia\Webwork\Resource\Manager;
use Appcia\Webwork\System\File;

class Form extends BasicForm
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * Constructor
     *
     * @param Manager $manager Resource manager
     */
    public function __construct(Manager $manager)
    {
        parent::__construct();

        $this->manager = $manager;
    }

    /**
     * Load resources using resource manager
     * Upload files or retrieve previously uploaded from temporaries
     * Use skipped fields parameter if for some resources should be not loaded but removed
     *
     * @param string       $token Form token
     * @param string|array $skip  Skipped field names
     *
     * @return Form
     */
    public function load($token, $skip = null)
    {
        $skipped = array();
        if ($skip !== null) {
            if (is_array($skip)) {
                $skipped = $skip;
            } else {
                $skipped = array($skip);
            }
        }

        foreach ($this->getFields() as $name => $field) {
            if ($field->getType() !== Field::FILE) {
                continue;
            }

            $resource = null;
            $params = array(
                'token' => $token,
                'key' => $name
            );

            // If field should be skipped remove associated resource
            if (in_array($name, $skipped)) {
                $this->manager->remove('upload', $params);
            } else {
                $data = $this->normalizeUpload($field->getValue());

                // If not, upload it or load from temporaries
                if (!empty($data)) {
                    $resource = $this->upload($token, $name, $data);
                } else {
                    $resource = $this->manager->load('upload', $params);
                }

                // For sure, resource file could be removed in a meanwhile
                if ($resource->getFile(false) === null) {
                    $resource = null;
                }
            }

            $this->set($name, $resource);
        }

        return $this;
    }

    /**
     * Unload resources previously loaded by resource manager
     * Remove files from temporaries
     *
     * @param string $token Token
     *
     * @return Form
     */
    public function unload($token)
    {
        foreach ($this->getFields() as $name => $field) {
            if ($field->getType() !== Field::FILE) {
                continue;
            }

            $this->manager->remove(
                'upload',
                array(
                    'token' => $token,
                    'key' => $name
                )
            );

            $this->set($name, null);
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

        $resource = $this->manager->save(
            'upload',
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
     * @throws Exception
     */
    public function normalizeUpload($data)
    {
        if (!is_array($data)) {
            throw new Exception('Uploaded data is not an array.' . PHP_EOL
                . 'Propably you just forget to add enctype multipart/form-data to form');
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