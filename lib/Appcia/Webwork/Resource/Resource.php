<?

namespace Appcia\Webwork\Resource;
use Appcia\Webwork\System\File;

/**
 * General file / URL representation for images, videos, anything...
 */
class Resource extends Type
{
    /**
     * Manager
     *
     * @var Manager
     */
    protected $manager;

    /**
     * Sub types
     *
     * @var Type[]
     */
    protected $types;

    /**
     * Mapping parameters
     *
     * @var array
     */
    protected $params;

    /**
     * Constructor
     *
     * @param Manager $manager Manager
     * @param string  $name    Name
     * @param array   $params  Mapping parameters
     */
    public function __construct(Manager $manager, $name, $params = array())
    {
        parent::__construct($this, $name);

        $this->manager = $manager;
        $this->params = (array) $params;

        $this->loadTypes();
    }

    /**
     *
     * @return $this
     */
    protected function loadTypes()
    {
        $config = $this->manager->getConfig($this->name);
        $types = isset($config['types'])
            ? (array) $config['types']
            : array();

        $this->types = array();
        foreach ($types as $name => $type) {
            if (!$type instanceof Type) {
                $type = Type::objectify($type, array($this, $name));
            }

            $this->types[$name] = $type;
        }

        return $this;
    }

    /**
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return Type[]
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return $this
     */
    public function remove()
    {
        foreach ($this->types as $type) {
            $type->getFile()
                ->remove();
        }

        $this->getFile()
            ->remove();

        $this->getFile()
            ->getDir()
            ->remove();

        return $this;
    }

    /**
     * @return File
     */
    public function getFile()
    {
        $config = $this->manager->getConfig($this->name);
        $path = $this->compilePath($config['path']);
        $file = new File($path);

        return $file;
    }

    /**
     * Get type by name
     *
     * @param string $type Type name
     *
     * @return Type
     * @throws \OutOfBoundsException
     */
    public function getType($type)
    {
        if (!isset($this->types[$type])) {
            throw new \OutOfBoundsException(sprintf("Resource type '%s' does not exist.", $type));
        }

        return $this->types[$type];
    }

    /**
     * Save (update) resource file
     *
     * @param mixed $source Source file
     *
     * @return $this
     */
    public function save($source)
    {
        $source = $this->extract($source);
        $target = $this->getFile();

        $source->copy($target);

        return $this;
    }

    /**
     * Extract file from various sources
     *
     * @param mixed $source
     *
     * @return File|null
     * @throws \InvalidArgumentException
     */
    protected function extract($source)
    {
        $file = null;

        if (is_string($source)) {
            $file = new File($source);
        }
        elseif ($source instanceof self) {
            $file = $source->getFile();
        }
        elseif (!$source instanceof File) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid source file type to be extracted: '%s'.",
                gettype($source)
            ));
        }

        return $file;
    }

    /**
     * Shorthand get type
     *
     * @param string $type Type name
     *
     * @return Type
     */
    public function __get($type)
    {
        return $this->getType($type);
    }
}

