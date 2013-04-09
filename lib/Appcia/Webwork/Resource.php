<?

namespace Appcia\Webwork;

use Appcia\Webwork\Resource\Manager;
use Appcia\Webwork\System\Dir;
use Appcia\Webwork\System\File;

class Resource
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var string
     */
    private $name;

    /**
     * Parameters (to be used in path generating)
     *
     * @var array
     */
    private $params;

    /**
     * Lazy loaded file
     *
     * @var File
     */
    private $file;

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
        $this->manager = $manager;
        $this->name = $name;
        $this->params = $params;
        $this->types = null;
        $this->file = null;
    }

    /**
     * Get origin factory
     *
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return File
     */
    public function getFile()
    {
        // @todo Generate path basing on manager config and parameter
        // @todo If extension is unknown guess using glob
        if ($this->file === null) {
            // @todo Lazy loading
            $this->file = new File('');
        }

        return $this->file;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        // @todo Generate paths in the same way like file for file
        if ($this->types === null) {
            // @todo Lazy loading
            $this->types = array();
        }

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

