<?

namespace Appcia\Webwork\Web\Form;

use Appcia\Webwork\Resource\Manager;
use Appcia\Webwork\Storage\Session;
use Appcia\Webwork\Web\Context;
use Appcia\Webwork\Web\Form;
use Appcia\Webwork\Web\Form\Field\File;

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

        foreach ($files as $name => $data) {
            $field = $this->getField($name);

            if ($field instanceof File) {
                $field->upload($data);
            }
        }

        return $this;
    }
}