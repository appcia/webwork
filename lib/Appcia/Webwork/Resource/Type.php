<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Core\Object;
use Appcia\Webwork\Core\Objector;
use Appcia\Webwork\Model\Template;
use Appcia\Webwork\Resource\Service\Processor;
use Appcia\Webwork\System\File;

/**
 * Subtype of resource (thumbnail, format derivation)
 */
class Type implements Object
{
    /**
     * Base resource
     *
     * @var Resource
     */
    protected $resource;

    /**
     * @var string
     */
    protected $name;

    /**
     * Allowed extensions for producing this type
     *
     * @var string[]
     */
    protected $extensions;

    /**
     * Producer
     *
     * @var Processor
     */
    protected $processor;

    /**
     * Constructor
     *
     * @param Resource $resource
     * @param string   $name
     */
    public function __construct(Resource $resource, $name)
    {
        $this->resource = $resource;
        $this->name = $name;

        $this->extensions = array();
    }

    /**
     * {@inheritdoc}
     */
    public static function objectify($data, $args = array())
    {
        return Objector::objectify($data, $args, get_called_class());
    }

    /**
     * Shorthand getting path to file
     *
     * @return string
     */
    public function __toString() {
        try {
            return (string) $this->getFile();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            return '';
        }
    }

    /**
     * @param boolean $process
     *
     * @return File
     */
    public function getFile($process = true)
    {
        $config = $this->resource->getManager()
            ->getConfig($this->resource->getName(), $this->name);

        $path = $this->compilePath($config['path']);

        $file = new File($path);
        $file->guess();

        if ($process && $this->isProcessable()) {
            $this->processor->run($this);
        }

        return $file;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function compilePath($path)
    {
        $params = $this->resource->getParams();

        $tpl = new Template($path);
        $tpl->setParams($params, File::WILDCARD);

        $path = $tpl->render();

        return $path;
    }

    /**
     * Check whether is processable (e.g thumbnail can be created)
     * If type file already exist also returns false
     *
     * @return boolean
     */
    protected function isProcessable()
    {
        $target = $this->getFile(false);
        $source = $this->getResource()
            ->getFile();

        if (!$source->exists() || $target->exists()) {
            return false;
        }

        if (!in_array($source->getExtension(), $this->extensions)) {
            return false;
        }

        return true;
    }

    /**
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return string[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @param string[] $extensions
     *
     * @return $this
     */
    public function setExtensions($extensions)
    {
        $this->extensions = (array) $extensions;

        return $this;
    }

    /**
     * @return Processor
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @param Processor $processor
     *
     * @return $this
     */
    public function setProcessor($processor)
    {
        if (!$processor instanceof Processor) {
            $processor = Processor::objectify($processor, array($this));
        }

        $this->processor = $processor;

        return $this;
    }


}