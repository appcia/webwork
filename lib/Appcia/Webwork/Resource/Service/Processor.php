<?

namespace Appcia\Webwork\Resource\Service;

use Appcia\Webwork\Resource\Service;
use Appcia\Webwork\Resource\Type;
use Appcia\Webwork\System\File;

/**
 * Creator for derivatives of base resource (thumbnails, format conversions etc)
 */
abstract class Processor extends Service
{
    /**
     * Base type
     *
     * @var Resource
     */
    protected $type;

    /**
     * Constructor
     *
     * @param Type $type
     */
    public function __construct(Type $type)
    {
        $manager = $type->getResource()
            ->getManager();

        parent::__construct($manager);

        $this->type = $type;
    }

    /**
     * @return Resource
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return File
     */
    public function run()
    {
        return parent::run();
    }
}