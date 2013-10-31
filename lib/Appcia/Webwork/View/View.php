<?

namespace Appcia\Webwork\View;

use Appcia\Webwork\View\Helper;
use Appcia\Webwork\View\Renderer;
use Appcia\Webwork\Web\App;

/**
 * Handles data binding with presentation files
 */
class View
{
    const MODULE_DELIMITER = ':';

    /**
     * Application
     *
     * @var App
     */
    protected $app;

    /**
     * Data
     *
     * @var array
     */
    protected $data;

    /**
     * Associated template
     *
     * @var string
     */
    protected $template;

    /**
     * Content renderer
     *
     * @var Renderer
     */
    protected $renderer;

    /**
     * Constructor
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        $this->data = array();
        $this->setRenderer(new Renderer\Php());
    }

    /**
     * Get a container
     *
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Append data
     *
     * @param array $data Data
     *
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = (string)$template;

        return $this;
    }

    /**
     * Get content renderer
     *
     * @return Renderer|Renderer\Php
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
     * @return $this
     */
    public function setRenderer($renderer)
    {
        if (!$renderer instanceof Renderer) {
            $renderer = Renderer::objectify($renderer);
        }

        $renderer->setView($this);
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Get template file path
     *
     * @param string $template Template
     * @param array  $paths    Search paths
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getTemplatePath($template = null, $paths = array())
    {
        if ($template === null) {
            $template = $this->template;
        }

        if (file_exists($template)) {
            return $template;
        }

        $paths = (array)$paths;

        // Handle '[module name]:[template path]' notation
        if (strpos($template, self::MODULE_DELIMITER) !== false) {
            $parts = explode(self::MODULE_DELIMITER, $template);

            if (count($parts) !== 2) {
                throw new \InvalidArgumentException(sprintf(
                    "Template file '%s' cannot has more than one module delimiter.",
                    $template
                ));
            }

            list ($module, $template) = $parts;
            array_unshift($paths, $this->getModulePath($module));
        }

        // For sure, add current module path
        array_unshift($paths, $this->getModulePath());

        // Last added on stack are most important
        $paths = array_reverse(array_unique($paths));

        // Search for template
        foreach ($paths as $path) {
            $file = $path . '/' . $template;

            if (file_exists($file)) {
                return $file;
            }
        }

        throw new \InvalidArgumentException(sprintf("Template file not found: '%s'", $template));
    }

    /**
     * Get path for views in specified module
     * If not specified, path is for current module
     *
     * @param null $module
     *
     * @return string
     */
    public function getModulePath($module = null)
    {
        $dispatcher = $this->app->getDispatcher();
        $path = $dispatcher->getModulePath($module) . '/view';

        return $path;
    }

    /**
     * Get rendered content
     *
     * @return string
     * @throws \LogicException
     */
    public function render()
    {
        if ($this->renderer === null) {
            throw new \LogicException('View renderer is not specified');
        }

        $content = $this->renderer->render();

        return $content;
    }
}