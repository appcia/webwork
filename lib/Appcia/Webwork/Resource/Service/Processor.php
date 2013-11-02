<?

namespace Appcia\Webwork\Resource\Service;

use Appcia\Webwork\Resource\Manager;
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
     * Constructor
     *
     * @param Manager  $manager
     * @param Resource $resource
     */
    public function __construct(Manager $manager, Resource $resource)
    {
        parent::__construct($manager);

        $this->resource = $resource;
    }

    /**
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return File
     */
    public function run()
    {
        return parent::run();
    }
}