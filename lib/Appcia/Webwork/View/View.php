<?

namespace Appcia\Webwork\View;

use Appcia\Webwork\Container;
use Appcia\Webwork\Exception\Exception;
use Appcia\Webwork\View\Helper;
use Appcia\Webwork\View\Renderer;

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
     * Associated template
     *
     * @var string
     */
    private $template;

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
        $this->setRenderer(new Renderer\Php());
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
            throw new Exception('View is not associated with any container. Invalid use');
        }

        return $this->container;
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
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
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
     * Get content renderer
     *
     * @return Renderer
     */
    public function getRenderer()
    {
        return $this->renderer;
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
            $renderer = Renderer::create($renderer);
        }

        $renderer->setView($this);
        $this->renderer = $renderer;

        return $this;
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