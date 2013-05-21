<?

namespace Appcia\Webwork;

use Appcia\Webwork\View\Helper;
use Appcia\Webwork\View\Renderer;
use Appcia\Webwork\View\Renderer\Ini;
use Appcia\Webwork\View\Renderer\Json;
use Appcia\Webwork\View\Renderer\Php;
use Appcia\Webwork\View\Renderer\Xml;

class View
{
    /**
     * DI container
     *
     * @var Container
     */
    private $container;

    /**
     * Data
     *
     * @var array
     */
    private $data;

    /**
     * Template file
     *
     * @var string
     */
    private $template;

    const PHP = 'php';
    const JSON = 'json';
    const XML = 'xml';
    const INI = 'ini';

    /**
     * Available content renderers
     *
     * @var array
     */
    private static $renderers = array(
        self::PHP,
        self::JSON,
        self::XML,
        self::INI
    );

    /**
     * Content renderer
     *
     * @var Renderer
     */
    private $renderer;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container = null)
    {
        $this->container = $container;

        $this->data = array();
        $this->setRenderer(new Php());
    }

    /**
     * Get a container
     * Can be used only in view created by dispatcher
     *
     * @return Container
     * @throws Exception
     */
    public function getContainer()
    {
        if ($this->container === null) {
            throw new Exception('Invalid use. There is no container associated with view');
        }

        return $this->container;
    }

    /**
     * Set data
     *
     * @param array $data Data
     *
     * @return View
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Append data
     *
     * @param array $data Data
     *
     * @return View
     */
    public function addData(array $data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set template
     *
     * @param string $template Path
     *
     * @return View
     */
    public function setTemplate($template)
    {
        $this->template = (string) $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Get path for views in current module
     *
     * @return string
     */
    public function getModulePath()
    {
        $dispatcher = $this->container->get('dispatcher');
        $path = $dispatcher->getModulePath() . '/view';

        return $path;
    }

    /**
     * Get available content renderers
     *
     * @return array
     */
    public static function getRenderers()
    {
        return self::$renderers;
    }

    /**
     * Set content renderer
     *
     * @param Renderer $renderer Renderer
     *
     * @return View
     * @throws Exception
     */
    public function setRenderer($renderer)
    {
        if (!$renderer instanceof Renderer) {
            $renderer = $this->createRenderer($renderer);
        } else {
            $renderer->setView($this);
        }

        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Create renderer from config
     *
     * @param string|array $data Data
     *
     * @return Renderer
     * @throws Exception
     */
    private function createRenderer($data)
    {
        $type = null;
        $config = null;

        // Parse data
        if (is_string($data)) {
            $type = $data;
        } elseif (is_array($data)) {
            if (!isset($data['type'])) {
                throw new Exception("Missing key 'type' in view renderer config");
            }
            $type = $data['type'];

            if (!empty($data['config'])) {
                $config = new Config($data['config']);
            }
        } else {
            throw new Exception('Invalid view renderer data');
        }

        // Create valid object
        $renderer = null;
        switch ($type) {
            case self::PHP:
                $renderer = new Php();
                break;
            case self::JSON:
                $renderer = new Json();
                break;
            case self::XML:
                $renderer = new Xml();
                break;
            case self::INI:
                $renderer = new Ini();
                break;
            default:
                throw new Exception(sprintf("Invalid or unsupported view renderer: '%s'", $renderer));
                break;
        }
        $renderer->setView($this);

        // Inject configuration
        if ($config !== null) {
            $config->inject($renderer);
        }

        return $renderer;
    }

    /**
     * Get content renderer
     *
     * @return Renderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }


    /**
     * Get template file path
     *
     * @param string $template Template
     *
     * @return string
     * @throws Exception
     */
    public function getTemplatePath($template = null)
    {
        if ($template === null) {
            $template = $this->template;
        }

        if (!file_exists($template)) {
            $moduleFile = $this->getModulePath() . '/' . $template;

            if (!file_exists($moduleFile)) {
                throw new Exception(sprintf("Template file not found: '%s'", $template));
            }

            $template = $moduleFile;

            return $template;
        }

        return $template;
    }

    /**
     * Get rendered content
     *
     * @return string
     * @throws Exception
     */
    public function render()
    {
        if ($this->renderer === null) {
            throw new Exception('View renderer not specified');
        }

        $content = $this->renderer->render();

        return $content;
    }
}