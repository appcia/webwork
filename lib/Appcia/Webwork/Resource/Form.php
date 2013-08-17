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
     * Constructor
     *
     * @param Context $context Use context
     * @param Manager $manager Resource manager
     */
    public function __construct(Context $context, Manager $manager)
    {
        $this->manager = $manager;
        parent::__construct($context);
    }

    /**
     * Load resources using resource manager
     * Upload files, retrieve previously uploaded from temporaries
     *
     * @param array|string $fields Field names
     *
     * @return $this
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function load($fields = null)
    {
        $fields = (array) $fields;

        $token = $this->getMetadata(self::CSRF);
        if ($token === null) {
            throw new \LogicException("Form resource loading requires active CSRF protection.");
        }

        foreach ($this->getFields() as $name => $field) {
            if (!in_array($name, $fields)) {
                continue;
            } elseif (!$field instanceof Field\File) {
                throw new \InvalidArgumentException(sprintf(
                    "Field '%s' resource cannot be loaded because its type is not a file.",
                    $name
                ));
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
        }

        return $this;
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
        $fields = (array) $fields;

        $token = $this->getMetadata(self::CSRF);
        if ($token === null) {
            throw new \LogicException("Form resource unloading requires active CSRF protection.");
        }

        foreach ($this->getFields() as $name => $field) {
            if (!in_array($name, $fields)) {
                continue;
            } elseif (!$field instanceof Field\File) {
                throw new \InvalidArgumentException(sprintf(
                    "Field '%s' resource cannot be unloaded because its type is not a file.",
                    $name
                ));
            }

            $this->manager->remove(
                Manager::UPLOAD,
                array(
                    'token' => $token,
                    'key' => $name
                )
            );

            $field->setValue(null);
        }

        return $this;
    }
}