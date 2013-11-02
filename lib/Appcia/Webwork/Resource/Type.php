<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Core\Object;
use Appcia\Webwork\Core\Objector;
use Appcia\Webwork\Model\Template;
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
     * Constructor
     *
     * @param Resource $resource
     * @param string   $path
     */
    public function __construct(Resource $resource, $name)
    {
        $this->resource = $resource;
        $this->name = $name;
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return File
     */
    public function getFile()
    {
        $config = $this->resource->getManager()
            ->getConfig($this->resource->getName(), $this->name);

        $path = $this->compilePath($config['path']);

        $file = new File($path);
        $file->guess();

        return $file;
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
     * Shorthand getting path to file
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getFile();
    }
}