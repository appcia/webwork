<?

namespace Appcia\Webwork;

use Appcia\Webwork\Resource\Manager;
use Appcia\Webwork\Resource\Type;

class Resource extends Type
{
    /**
     * Manager
     *
     * @var Manager
     */
    private $manager;

    /**
     * Name
     *
     * @var string
     */
    private $name;

    /**
     * Loaded types
     *
     * @var array
     */
    private $types;

    /**
     * Instantiated processor objects
     *
     * @var array
     */
    private $processors;

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
     * @param $type Type name
     *
     * @return Type
     * @throws Exception
     */
    public function getType($type)
    {
        if ($this->types === null) {
            $this->types = $this->loadTypes();
        }

        if (!isset($type, $this->types)) {
            throw new Exception(sprintf("Invalid type specified '%s'", $type));
        }

        return $this->types[$type];
    }

    /**
     * Create subtypes basing on original resource
     *
     * @return Resource
     * @throws Exception
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
            $processor = $this->getProcessor($name, $config);

            $settings = null;
            if (!empty($config['processor']['settings'])) {
                $settings = $config['processor']['settings'];
            }

            $files = $processor->process($this, $settings);
            if (!is_array($files)) {
                throw new Exception(sprintf("Processor for resource type '%s' should return files as array"));
            }

            $params = $this->getParams();
            $params['type'] = $name;

            foreach ($files as $fileKey => $file) {
                $params['key'] = $fileKey;

                $type = new Type($this, $config['path'], $params);
                $types[$name] = $type;

                $targetFile = $type->getFile();
                if ($targetFile->exists()) {
                    $targetFile->remove();
                }

                $file->move($targetFile);
            }
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
     * Get processor for creating derivative types basing on original resource
     *
     * @param string $type   Type name
     * @param array  $config Configuration for type
     *
     * @return Manager
     * @throws Exception
     */
    private function getProcessor($type, array $config)
    {
        if (empty($config['processor'])) {
            throw new Exception(sprintf("Processor configuration for resource type '%s' not found", $type));
        }

        if (empty($config['processor']['class'])) {
            throw new Exception(sprintf("Cannot find class name for resource type '%s'", $type));
        }

        $class = $config['processor']['class'];

        if (isset($this->processors[$class])) {
            return $this->processors[$class];
        }

        if (!class_exists($class)) {
            throw new Exception(sprintf("Processor class '%s' does not exist", $class));
        }

        $processor = new $class();
        $processor->setManager($this->manager);

        $this->processors[$class] = $processor;

        return $processor;
    }

}

