<?

namespace Appcia\Webwork\View\Renderer;

use Appcia\Webwork\View\Helper;
use Appcia\Webwork\View\Renderer;

/**
 * Native PHP renderer
 *
 * @package Appcia\Webwork\View\Renderer
 */
class Php extends Renderer
{
    /**
     * Registered view helpers
     *
     * @var array
     */
    private $helpers;

    /**
     * Output compression
     *
     * @var boolean
     */
    private $sanitization;

    /**
     * Template extending map
     * Block name is mapped to template
     *
     * @var array
     */
    private $extends;

    /**
     * Block output buffers
     *
     * @var array
     */
    private $buffer;

    /**
     * Stack for block names
     * Used for checking template content (begin / end pairing)
     *
     * @var array
     */
    private $stack;

    /**
     * Captured block contents
     *
     * @var array
     */
    private $blocks;

    /**
     * Template path stack
     *
     * @var array
     */
    private $paths;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->helpers = array();
        $this->sanitization = false;

        $this->buffer = array();
        $this->stack = array();
        $this->extends = array();
        $this->blocks = array();
        $this->paths = array();
    }

    /**
     * Call view helper using $this->{helperName}({args...}) in templates
     *
     * @param $name Helper name
     * @param $args Helper arguments
     *
     * @return mixed
     * @throws \ErrorException
     */
    public function __call($name, $args)
    {
        $helper = $this->getHelper($name);

        $method = mb_strtolower($name);
        $callback = array($helper, $method);

        if (!is_callable($callback)) {
            throw new \ErrorException(sprintf("View helper '%s' does not have accessible method: '%s", $name, $method));
        }

        $result = call_user_func_array($callback, $args);

        return $result;
    }

    /**
     * Get helper by name
     *
     * @param string $name Name
     *
     * @return Helper
     */
    public function getHelper($name)
    {
        if (!isset($this->helpers[$name])) {
            $context = $this->getView()
                ->getApp()
                ->getContext();

            $helper = $this->createHelper($name);
            $helper->setView($this->getView())
                ->setContext($context);

            $this->helpers[$name] = $helper;
        }

        return $this->helpers[$name];
    }

    /**
     * Create helper by name
     * Search for valid class name in all modules
     *
     * @param string $name Name
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    private function createHelper($name)
    {
        $class = 'Appcia\\Webwork\\View\\Helper\\' . ucfirst($name);

        if (class_exists($class)) {
            return new $class();
        }

        $modules = $this->getView()
            ->getApp()
            ->getModules();

        foreach ($modules as $module) {
            $class = $module->getNamespace() . '\\View\\Helper\\' . ucfirst($name);

            if (class_exists($class)) {
                return new $class();
            }
        }

        throw new \InvalidArgumentException(sprintf("View helper '%s' cannot be found. There is no valid class in any module", $class));
    }

    /**
     * Get registered helpers
     *
     * @return array
     */
    public function getHelpers()
    {
        return $this->helpers;
    }

    /**
     * Set sanitization enabled / disabled
     *
     * @param boolean $sanitization Flag
     *
     * @return Php
     */
    public function setSanitization($sanitization)
    {
        $this->sanitization = (bool) $sanitization;

        return $this;
    }

    /**
     * Check whether sanitization is enabled
     *
     * @return boolean
     */
    public function isSanitization()
    {
        return $this->sanitization;
    }

    /**
     * Get captured block
     *
     * @param string $name Block name
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getBlock($name)
    {
        if (!isset($this->blocks[$name])) {
            throw new \InvalidArgumentException(sprintf("Block '%s' does not exist", $name));
        }

        return $this->blocks[$name];
    }

    /**
     * Start block capturing
     *
     * @param string      $name     Block name
     * @param string      $template Template to be extended
     * @param string|null $module   Module in which template exists
     *
     * @throws \LogicException
     */
    public function beginBlock($name, $template = null, $module = null)
    {
        if ($template !== null) {
            if ($module !== null) {
                $path = $this->getView()
                    ->getModulePath($module);
                $template = $path . '/' . $template;
            }

            if (isset($this->extends[$name])) {
                throw new \LogicException(sprintf("Block name that will be extended is already used: '%s'.", $name));
            }

            // Associate block with extending
            $this->extends[$name] = $template;
            $this->pushTemplate($template);
        }

        // Stop and save current output capturing
        $this->buffer[$name] = ob_get_clean();

        // Start capturing new block
        ob_start();

        array_push($this->stack, $name);
    }

    /**
     * Push template path onto stack
     * Used for relative paths in extended templates
     *
     * @param string $template Template
     *
     * @return Php
     */
    protected function pushTemplate($template)
    {
        $path = dirname($template);
        $current = in_array($path, array('', '.'));

        if (!$current) {
            array_push($this->paths, $path);
        }

        return $this;
    }

    /**
     * End block capturing
     *
     * @param $name
     *
     * @return mixed|string
     * @throws \LogicException
     */
    public function endBlock($name = null)
    {
        // Retrieve block name from stack, check that match if specified
        $check = array_pop($this->stack);
        if ($name === null) {
            $name = $check;
        } else if ($name !== $check) {
            throw new \LogicException(sprintf("Block begin / end structure is not consistent." . PHP_EOL
            . "Problem occurred with: '%s'", $name));
        }

        // Get captured block
        $content = ob_get_clean();

        // Continue previous output capturing
        ob_start();

        echo $this->buffer[$name];
        unset($this->buffer[$name]);

        // Set block only when there is no previous
        if (empty($this->blocks[$name])) {
            $this->blocks[$name] = $content;
        }

        // Check that block must be extended
        if (isset($this->extends[$name])) {
            $template = $this->extends[$name];
            $this->popTemplate($template);

            unset($this->extends[$name]);
            echo $this->render($template);
        } else {
            $this->block($name);
        }
    }

    /**
     * Pop template path from stack
     * Used for relative paths in extended templates
     *
     * @param string $template Template
     *
     * @return Php
     */
    protected function popTemplate($template)
    {
        $path = dirname($template);
        $current = in_array($path, array('', '.'));

        if (!$current) {
            array_pop($this->paths);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render($template = null)
    {
        $this->pushTemplate($template);
        $content = $this->capture($template);
        $this->popTemplate($template);

        if ($this->sanitization) {
            $content = $this->sanitize($content);
        }

        return $content;
    }

    /**
     * Capture included file
     *
     * @param string $template Template
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function capture($template)
    {
        $file = $this->getView()
            ->getTemplatePath($template, $this->paths);
        $data = $this->getView()
            ->getData();

        extract($data);
        ob_start();

        if (!is_file($file)) {
            throw new \InvalidArgumentException(sprintf("Template file does not exist: '%s'.", $file));
        }

        include $file;

        $content = ob_get_clean();

        return $content;
    }

    /**
     * Sanitize content (remove white characters)
     *
     * @param string $content
     *
     * @return mixed
     */
    protected function sanitize($content)
    {
        $search = array(
            '/\>[^\S ]+/s',
            '/[^\S ]+\</s',
            '/(\s)+/s'
        );
        $replace = array(
            '>',
            '<',
            '\\1'
        );
        $content = preg_replace($search, $replace, $content);

        return $content;
    }
}