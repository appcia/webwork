<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Data\Form as BasicForm;
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
     *
     * @param string $token Form token
     *
     * @return Form
     */
    public function load($token)
    {
        foreach ($this->getFields() as $name => $field) {
            if (!$field->isUploadable()) {
                continue;
            }

            // Retrieve file from temporaries
            $resource = $this->manager->load(
                'upload',
                array(
                    'token' => $token,
                    'key' => $name
                )
            );

            // Service file upload
            $data = $this->normalizeUpload($field->getValue());
            if (!empty($data)) {
                $resource = $this->upload($token, $name, $data);
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
            if (!$field->isUploadable()) {
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

}