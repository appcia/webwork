<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Web\Form\Field;
use Appcia\Webwork\Web\Form\Secure;
use Appcia\Webwork\Resource\Manager;
use Appcia\Webwork\System\File;
use Appcia\Webwork\Web\Context;
use Appcia\Webwork\Web\Request;

/**
 * Form with resource service (upload with temporary state)
 */
class Form extends Secure
{
    /**
     * Resource manager
     *
     * @var Manager
     */
    protected $manager;

    /**
     * At least one field is unloaded
     *
     * @var boolean
     */
    protected $unloaded;

    /**
     * At least one field is loaded
     *
     * @var boolean
     */
    protected $loaded;

    /**
     * Constructor
     *
     * @param Context $context Use context
     * @param Manager $manager Resource manager
     */
    public function __construct(Context $context, Manager $manager)
    {
        $this->manager = $manager;
        $this->loaded = false;
        $this->unloaded = false;

        parent::__construct($context);
    }

    /**
     * Load resources using resource manager
     * Upload files, retrieve previously uploaded from temporaries
     *
     * @param mixed        $files  Files
     * @param array|string $fields Field names
     *
     * @return $this
     * @throws \LogicException
     */
    public function upload($files, $fields = null)
    {
        $token = $this->getMetadata(self::CSRF);
        if ($token === null) {
            throw new \LogicException(
                "Form resource loading requires active CSRF protection.'
                .' Please be sure that form is verified before uploading."
            );
        }

        foreach ($this->filterFields() as $name => $field) {
            if ($fields !== null && !in_array($name, (array) $fields)) {
                continue;
            }

            if (!isset($files[$name])) {
                throw new \LogicException(sprintf("Form file field '%s' cannot be loaded. No data provided.", $name));
            }

            $data = $this->manager->normalizeUpload($files[$name]);
            $params = array(
                'token' => $token,
                'key' => $name
            );

            // Try to get from upload, if not get from temporary location
            $resource = null;
            if (!empty($data)) {
                $resource = $this->manager->upload($data, $params);
            } else {
                $resource = $this->manager->load(Manager::UPLOAD, $params);
            }

            // Set if exists
            if ($resource->exists()) {
                $field->setValue($resource);
                $this->loaded = true;
            }
        }

        return $this;
    }

    /**
     * Get all file fields
     *
     * @return Field[]
     */
    protected function filterFields()
    {
        $fields = array();

        foreach ($this->fields as $name => $field) {
            if ($field instanceof Field\File) {
                $fields[$name] = $field;
            }
        }

        return $fields;
    }

    /**
     * Unload resources previously loaded by resource manager
     * Remove files from temporaries
     *
     * @param string $fields Field names
     *
     * @return $this
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function unload($fields = null)
    {
        $token = $this->getMetadata(self::CSRF);
        if ($token === null) {
            throw new \LogicException("Form resource unloading requires active CSRF protection.");
        }

        $this->unloaded = false;
        foreach ($this->filterFields() as $name => $field) {
            if ($fields !== null && !in_array($name, (array) $fields)) {
                continue;
            }

            // Temporary resource from upload
            $resource = $this->manager->load(
                Manager::UPLOAD,
                array(
                    'token' => $token,
                    'key' => $name
                )
            );

            // Previously created from editing
            if (!$resource->exists()) {
                $value = $field->getValue();
                if ($value instanceof Resource) {
                    $resource = $value;
                }
            }

            // Remove if exist
            if ($resource->exists()) {
                $resource->remove();

                $field->setValue(null);
                $this->unloaded = true;
            }
        }

        return $this;
    }

    /**
     * Check whether at least one field with file is uploaded
     *
     * @return boolean
     */
    public function isUploaded()
    {
        return $this->loaded;
    }

    /**
     * Check whether at least one field with file was uploaded (removed)
     *
     * @return boolean
     */
    public function isUnloaded()
    {
        return $this->unloaded;
    }
}