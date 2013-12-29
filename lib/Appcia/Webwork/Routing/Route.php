<?

namespace Appcia\Webwork\Routing;

use Appcia\Webwork\Core\Object;
use Appcia\Webwork\Core\Objector;
use Appcia\Webwork\Data\Converter;

/**
 * Associates URI address with action to be executed
 *
 * @package Appcia\Webwork\Routing
 */
class Route implements Object
{
    /**
     * Name
     *
     * @var string
     */
    protected $name;

    /**
     * Path representation
     *
     * @var Path
     */
    protected $path;

    /**
     * Module name
     *
     * @var string
     */
    protected $module;

    /**
     * Control name
     *
     * @var string
     */
    protected $control;

    /**
     * Action name
     *
     * @var string
     */
    protected $action;

    /**
     * Template file to be rendered
     *
     * @var string
     */
    protected $template;

    /**
     * Parameter names
     *
     * @var array
     */
    protected $params;

    /**
     * Alias for name
     *
     * @var string|null
     */
    protected $alias;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->params = array();
        $this->template = '*.html.php';
    }

    /**
     * Creator
     *
     * @param mixed $data Route data
     * @param array $args Constructor arguments
     *
     * @return $this
     */
    public static function objectify($data, $args = array())
    {
        if (!isset($data['name'])) {
            $data['name'] = self::generateName(
                $data['module'],
                $data['control'],
                $data['action']
            );
        }

        return Objector::objectify($data, $args, get_called_class());
    }

    /**
     * Generate unique name
     *
     * @param string      $module     Module name
     * @param string      $control Control path (or null for only module name)
     * @param string|null $action     Action name (or null for only control name)
     *
     * @return string
     */
    public static function generateName($module, $control = null, $action = null)
    {
        $parts = explode('/', $module);

        if ($control !== null) {
            $parts = array_merge($parts, explode('/', $control));
        }

        if ($action !== null) {
            $parts = array_merge($parts, array($action));
        }

        $converter = new Converter();
        foreach ($parts as $key => $value) {
            $parts[$key] = $converter->camelToDashed($value);
        }

        $name = implode('-', $parts);

        return $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setName($name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Route name cannot be empty.');
        }

        $this->name = (string) $name;

        return $this;
    }

    /**
     * Get path
     *
     * @return Path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set path
     *
     * @param string $path Path
     *
     * @return $this
     */
    public function setPath($path)
    {
        if (!$path instanceof Path) {
            $path = new Path($this, $path);
        }

        $this->path = $path;
        $this->updateParams($path);

        return $this;
    }

    /**
     * Complete params configuration basing on names defined in path
     *
     * @return $this
     */
    protected function updateParams()
    {
        $params = array_keys($this->path->getParams());

        foreach ($params as $param) {
            if (!isset($this->params[$param])) {
                $this->params[$param] = array();
            }
        }

        return $this;
    }

    /**
     * Pattern for retrieving params from request
     * Used by router if route has any params
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->path->getRegExp();
    }

    /**
     * Get parameter names
     *
     * @return array
     */
    public function getParams()
    {
        $this->updateParams();

        return $this->params;
    }

    /**
     * Set parameter config
     *
     * @param array $params Config
     *
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get action name
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set action name
     *
     * @param $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = (string) $action;

        return $this;
    }

    /**
     * Get unique action name
     *
     * @return string
     */
    public function getActionName()
    {
        $name = $this->generateName($this->module, $this->control, $this->action);

        return $name;
    }

    /**
     * Get control name
     *
     * @return mixed
     */
    public function getControl()
    {
        return $this->control;
    }

    /**
     * Set control name
     *
     * @param $control
     *
     * @return $this
     */
    public function setControl($control)
    {
        $this->control = (string) $control;

        return $this;
    }

    /**
     * Generate unique control name
     *
     * @return string
     */
    public function getControlName()
    {
        $name = $this->generateName($this->module, $this->control);

        return $name;
    }

    /**
     * Get module name
     *
     * @return mixed
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set module name
     *
     * @param $module
     *
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = (string) $module;

        return $this;
    }

    /***
     * @return string
     */
    public function getModuleName()
    {
        return $this->generateName($this->module);
    }

    /**
     * Get template file to be rendered
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set template file to be rendered
     *
     * @param string $template
     *
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = (string) $template;

        return $this;
    }

    /**
     * Get name alias
     *
     * @return null|string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set name alias
     *
     * @param null|string $alias Alias
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function setAlias($alias)
    {
        if (empty($alias)) {
            throw new \InvalidArgumentException('Route alias cannot be empty.');
        }

        $this->alias = $alias;

        return $this;
    }
}