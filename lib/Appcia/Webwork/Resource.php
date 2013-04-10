<?

namespace Appcia\Webwork;

use Appcia\Webwork\Resource\Manager;
use Appcia\Webwork\Resource\Type;
use Appcia\Webwork\System\Dir;
use Appcia\Webwork\System\File;

class Resource extends Type
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var array
     */
    private $types;

    /**
     * Constructor
     *
     * @param Manager      $manager Manager
     * @param string       $name    Name
     * @param string|array $params  Parameters
     */
    public function __construct(Manager $manager, $name, array $params)
    {
        parent::__construct($manager, $name, $params);

        $this->types = array();
    }

    /**
     * @return array
     */
    public function getTypes()
    {


        return $this->types;
    }

    public function getType($type)
    {
        if (!array_key_exists($type, $this->types)) {
            throw new Exception(sprintf("Invalid resource type: '%s'", $type));
        }

        return $this->types[$type];
    }

}

