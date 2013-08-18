<?

namespace Appcia\Webwork\Resource;

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
     * Name
     *
     * @var string
     */
    protected $name;

    /**
     * Loaded types
     *
     * @var array
     */
    protected $types;

    /**
     * Registered processors
     *
     * @var array
     */
    protected $processors;

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
        $this->processors = array();
        $this->types = null;

        $config = $manager->getConfig($name);

        parent::__construct($this, $config['path'], $params);
    }

    /**
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get all types
     *
     * @return array
     */
    public function getTypes()
    {
        if ($this->types === null) {
            $this->types = $this->loadTypes();
        }

        return $this->types;
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
        if ($this->types === null) {
            $this->types = $this->loadTypes();
        }

        if (!isset($type, $this->types)) {
            throw new \OutOfBoundsException(sprintf("Resource type '%s' is invalid.", $type));
        }

        return $this->types[$type];
    }

    /**
     * Create subtypes basing on original resource
     *
     * @return $this
     * @throws \ErrorException
     */
    public function createTypes()
    {
        $config = $this->manager->getConfig($this->name);
        if (empty($config['type'])) {
            return $this;
        }

        $types = array();
        $configs = $config['type'];
        foreach ($configs as $name => $config) {
            $processor = $this->manager->getProcessor($name, $config);

            $settings = null;
            if (!empty($config['processor']['settings'])) {
                $settings = $config['processor']['settings'];
            }

            $file = $processor->setResource($this)
                ->setSettings($settings)
                ->run($this, $settings);

            $params = $this->getParams();
            $params['type'] = $name;

            $type = new Type($this, $config['path'], $params);
            $types[$name] = $type;

            $target = $type->getFile();
            if ($target->exists()) {
                $target->remove();
            }

            $file->move($target);
        }

        $this->types = $types;

        return $this;
    }

    /**
     * Get processed types
     *
     * @return array
     */
    public function loadTypes()
    {
        $config = $this->manager->getConfig($this->name);

        if (empty($config['type'])) {
            return array();
        }

        $types = array();
        $configs = $config['type'];
        foreach ($configs as $name => $config) {
            $params = $this->getParams();
            $params['type'] = $name;

            $type = new Type($this, $config['path'], $params);
            $types[$name] = $type;
        }

        return $types;
    }

    /**
     * Remove files using manager
     *
     * @return $this
     */
    public function remove()
    {
        $this->manager->remove($this->name, $this->params);

        return $this;
    }

    /**
     * Save files using manager
     *
     * @param mixed $source Source file
     *
     * @return $this
     */
    public function save($source)
    {
        $this->manager->save($this->name, $this->params, $source);

        return $this;
    }

    /**
     * Check existing whether associated file exists
     *
     * @return boolean
     */
    public function exists()
    {
        $file = $this->getFile();
        $flag = ($file !== null) && $file->exists();

        return $flag;
    }
}

