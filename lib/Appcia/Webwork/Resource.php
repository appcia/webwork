<?

namespace Appcia\Webwork;

use Appcia\Webwork\Resource\Manager;
use Appcia\Webwork\Resource\Type;

class Resource extends Type
{
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
        if (!isset($type, $this->types)) {

        }

        return $this->types[$type];
    }

}

