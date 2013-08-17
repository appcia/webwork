<?

namespace Appcia\Webwork\Resource\Service;

use Appcia\Webwork\Resource\Resource;
use Appcia\Webwork\Resource\Service;
use Appcia\Webwork\System\File;

/**
 * Creator for derivatives of base resource (thumbnails, format conversions etc)
 */
abstract class Processor extends Service
{
    /**
     * Base resource
     *
     * @var Resource
     */
    protected $resource;

    /**
     * Runtime settings
     *
     * @var mixed
     */
    protected $settings;

    /**
     * Set base resource
     *
     * @param Resource $resource
     *
     * @return $this
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get resource to be processed
     *
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set runtime settings
     *
     * @param mixed $settings
     *
     * @return $this
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Get runtime settings
     *
     * @return mixed
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return File
     */
    public function run()
    {
        return parent::run();
    }
}