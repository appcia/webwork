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
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Shorthand getting path to file
     *
     * @return string
     */
    public function __toString() {
        $file = (string) $this->getFile();

        return $file;
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

        if ($process && !$file->exists() && in_array($file->getExtension(), $this->extensions)) {
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
        $tpl->setParams($params);

        $path = $tpl->render();

        return $path;
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
     * @return string[]
     */
    public function getExtensions()
    {
        return $this->extensions;
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

    /**
     * @return Processor
     */
    public function getProcessor()
    {
        return $this->processor;
    }


}