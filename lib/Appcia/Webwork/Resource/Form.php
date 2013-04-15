<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Data\Form as BasicForm;
use Appcia\Webwork\Data\Form\Field;
use Appcia\Webwork\Exception;
use Appcia\Webwork\Resource\Manager;
use Appcia\Webwork\System\File;

class Form extends BasicForm
{
    const METADATA_SKIPPED_RESOURCE = 'skippedResource';

    /**
     * @var Manager
     */
    private $manager;

    /**
     * At least one resource has changed its skip status
     *
     * @var bool
     */
    private $skipChanged;

    /**
     * Constructor
     *
     * @param Manager $manager Resource manager
     */
    public function __construct(Manager $manager)
    {
        parent::__construct();

        $this->manager = $manager;
        $this->skipChanged = false;
    }

    /**
     * Load resources using resource manager
     * Upload files, retrieve previously uploaded from temporaries or pass resources directly
     *
     * @param string $token     Form token
     * @param array  $resources Existing resources (useful in edit forms)
     *
     * @return Form
     */
    public function load($token, array $resources = array())
    {
        foreach ($this->getFields() as $name => $field) {
            if ($field->getType() !== Field::FILE) {
                continue;
            }

            $resource = null;
            $params = array(
                'token' => $token,
                'key' => $name
            );

            $value = $field->getValue();
            $data = $this->normalizeUpload($value);

            if (!empty($data)) {
                $resource = $this->upload($token, $name, $data);
                $this->unskip($name, true);
            }

            if (!$this->isSkipped($name)) {
                $resource = $this->manager->load('upload', $params);

                if (!$resource->exists()) {
                    $resource = isset($resources[$name]) ? $resources[$name] : null;
                }
            }

            $field->setValue($resource);
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

            $field->setValue(null);
        }

        return $this;
    }

    /**
     * Mark field with resource to be skipped
     *
     * @param string $name  Field name
     * @param bool   $quiet Do not affect skip changed flag
     *
     * @return Form
     * @throws Exception
     */
    public function skip($name, $quiet = false)
    {
        if (empty($name)) {
            return $this;
        }

        $field = $this->getField($name);
        if ($field->getType() !== Field::FILE) {
            throw new Exception(sprintf("Invalid field name to be skipped in resource loading '%s'", $name));
        }

        $skipped = $this->getSkipped();
        if (!in_array($name, $skipped)) {
            $skipped[] = $name;

            if (!$quiet) {
                $this->skipChanged = true;
            }
        }
        $this->setSkipped($skipped);

        return $this;
    }

    /**
     * Unmark field with file to be not skipped
     *
     * @param string $name  Field name
     * @param bool   $quiet Do not affect skip changed flag
     *
     * @return Form
     * @throws Exception
     */
    public function unskip($name, $quiet = false)
    {
        if (empty($name)) {
            return $this;
        }

        $field = $this->getField($name);
        if ($field->getType() !== Field::FILE) {
            throw new Exception(sprintf("Invalid field name to be unskipped in resource loading '%s'", $name));
        }

        $skipped = $this->getSkipped();
        $key = array_search($name, $skipped);
        if ($key !== false) {
            unset($skipped[$key]);

            if (!$quiet) {
                $this->skipChanged = true;
            }
        }

        $this->setSkipped($skipped);

        return $this;
    }

    /**
     * Check whether field with file has changed its skip status
     *
     * @return bool
     */
    public function skipChanged()
    {
        return $this->skipChanged;
    }

    /**
     * Check whether field with file is skipped
     *
     * @param string $name Field name
     *
     * @return bool
     */
    public function isSkipped($name)
    {
        if (!$this->hasField($name)) {
            return false;
        }

        $names = $this->getSkipped();
        $skipped = in_array($name, $names);

        return $skipped;
    }

    /**
     * Get fields with file which are marked as skipped
     *
     * @return array
     */
    public function getSkipped()
    {
        $metadata = $this->getMetadata();
        $skipped = array();
        if (isset($metadata[self::METADATA_SKIPPED_RESOURCE])) {
            $skipped = $metadata[self::METADATA_SKIPPED_RESOURCE];
        }

        return $skipped;
    }

    private function setSkipped(array $names)
    {
        $metadata[self::METADATA_SKIPPED_RESOURCE] = $names;
        $this->setMetadata($metadata);

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