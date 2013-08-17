<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Web\Context;
use Appcia\Webwork\Data\Form as BaseForm;
use Appcia\Webwork\Data\Form\Field;
use Appcia\Webwork\Resource\Manager;
use Appcia\Webwork\System\File;

/**
 * Form with resource service (upload with temporary state)
 *
 * @package Appcia\Webwork\Resource
 */
class Form extends BaseForm
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
     * @param array|string $fields Field names
     *
     * @return $this
     * @throws \LogicException
     */
    public function load($fields = null)
    {
        $token = $this->getMetadata(self::CSRF);
        if ($token === null) {
            throw new \LogicException("Form resource loading requires active CSRF protection.");
        }

        foreach ($this->filterFields() as $name => $field) {
            if ($fields !== null && !in_array($name, (array) $fields)) {
                continue;
            }

            $resource = null;
            $params = array(
                'token' => $token,
                'key' => $name
            );

            $value = $field->getValue();
            $data = $this->manager->normalizeUpload($value);

            if (!empty($data)) {
                $resource = $this->manager->upload($data, $params);
            } else {
                $resource = $this->manager->load(Manager::UPLOAD, $params);
            }

            $field->setValue($resource);
            $this->loaded = true;
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

            $this->manager->remove(
                Manager::UPLOAD,
                array(
                    'token' => $token,
                    'key' => $name
                )
            );

            $field->setValue(null);
            $this->unloaded = true;
        }

        return $this;
    }

    /**
     * Check whether at least one field with file is loaded
     *
     * @return boolean
     */
    public function isLoaded()
    {
        return $this->loaded;
    }

    /**
     * Check whether at least one field with file is unloaded
     *
     * @return boolean
     */
    public function isUnloaded()
    {
        return $this->unloaded;
    }
}