<?

namespace Appcia\Webwork\Web\Form;

use Appcia\Webwork\Resource\Resource;
use Appcia\Webwork\Web\Context;
use Appcia\Webwork\Storage\Session;
use Appcia\Webwork\Web\Form;
use Appcia\Webwork\Resource\Manager;

/**
 * Form uploader
 */
class Uploader extends Secured
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
     * @param Context $context Context
     * @param Manager $manager Resource manager
     */
    public function __construct(Context $context, Manager $manager)
    {
        parent::__construct($context);

        $this->manager = $manager;
    }

    /**
     * Get resource manager
     *
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Upload multiple files at once
     * Fill field values with resources
     *
     * @param array $files Request files data
     *
     * @return $this
     * @throws \LogicException
     */
    public function upload($files)
    {
        if (empty($files)) {
            return $this;
        }

        $token = $this->getMetadata(static::CSRF);
        if ($token === null) {
            throw new \LogicException('Form CSRF protection must be enabled to use uploader.');
        }

        $params = array('token' => $token);

        foreach ($files as $key => $data) {
            $field = $this->getField($key);

            $data = $this->manager->normalizeUpload($data);
            if ($data === null) {
                continue;
            }

            $params['key'] = $key;
            $resource = $this->manager->upload($data, $params);

            if ($resource !== null && $resource->getFile()->exists()) {
                $field->setValue($resource);
            }
        }

        return $this;
    }

    /**
     * Remove uploaded file
     *
     * @param string $field Field name
     *
     * @return $this
     */
    public function unload($field)
    {
        $field = $this->getField($field);
        $resource = $field->getValue();

        if ($resource instanceof Resource) {
            $resource->remove();
            $field->setValue(null);
        }

        return $this;
    }
}