<?

namespace Appcia\Webwork;

use Appcia\Webwork\Resource\Manager;
use Appcia\Webwork\Resource\Type;

class Resource extends Type
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
     * @var array
     */
    private $types;

    /**
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
        $this->types = array();
        $this->processors = array();

        $config = $manager->getConfig($name);
        parent::__construct($config['path'], $params);
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
        return $this->types;
    }

    /**
     * Get type by name
     *
     * @param $type
     *
     * @return Type
     * @throws Exception
     */
    public function getType($type)
    {
        if (!isset($type, $this->types)) {
            throw new Exception(sprintf("Invalid type specified '%s'", $type));
        }

        return $this->types[$type];
    }

    /**
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
        foreach ($configs as $typeName => $config) {
            $processor = $this->getProcessor($typeName, $config);

            $settings = null;
            if (!empty($config['processor']['settings'])) {
                $settings = $config['processor']['settings'];
            }

            $files = $processor->process($this, $settings);
            if (!is_array($files)) {
                throw new Exception(sprintf("Processor for resource type '%s' should return files as array"));
            }

            $params = $this->getParams();
            $params['type'] = $typeName;

            foreach ($files as $fileKey => $file) {
                $params['key'] = $fileKey;

                $type = new Type($config['path'], $params);
                $types[] = $type;

                $file->move($type->getFile(true));
            }
        }

        $this->types = $types;

        return $this;
    }

    /**
     * Get processor for creating derivative types basing on original resource
     *
     * @param string $type
     * @param array  $config
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

