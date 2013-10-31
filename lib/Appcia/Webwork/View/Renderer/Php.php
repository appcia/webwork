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
    protected $helpers;

    /**
     * Output compression
     *
     * @var boolean
     */
    protected $sanitization;

    /**
     * Template extending map
     * Block name is mapped to template
     *
     * @var array
     */
    protected $extends;

    /**
     * Block output buffers
     *
     * @var array
     */
    protected $buffer;

    /**
     * Stack for block names
     * Used for checking template content (begin / end pairing)
     *
     * @var array
     */
    protected $stack;

    /**
     * Captured block contents
     *
     * @var array
     */
    protected $blocks;

    /**
     * Shared view data
     *
     * @var array
     */
    protected $data;

    /**
     * Template path stack
     *
     * @var array
     */
    protected $paths;

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
        $this->data = array();
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
            $app = $this->getView()
                ->getApp();

            $context = $app->getContext();
            $config = $app->getConfig()
                ->grab('view.helper')
                ->set('class', $name);

            $helper = Helper::objectify($config, array($context));

            $helper->setView($this->getView())
                ->setContext($context);

            $this->helpers[$name] = $helper;
        }

        return $this->helpers[$name];
    }

    /**
     * Get view shared variable
     *
     * @param string $name Name
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function & __get($name)
    {
        if (!array_key_exists($name, $this->data)) {
            throw new \InvalidArgumentException(sprintf("View shared variable '%s' does not exist.", $name));
        }

        return $this->data[$name];
    }

    /**
     * Set view shared variable (as property)
     *
     * @param string $name  Name
     * @param mixed  $value Value
     *
     * @return $this
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;

        return $this;
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
     * @return $this
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
     * Start block capturing
     *
     * @param string $name     Block name
     * @param string $template Template to be extended
     *
     * @throws \LogicException
     */
    public function beginBlock($name, $template = null)
    {
        if ($template !== null) {

            if (isset($this->extends[$name])) {
                throw new \LogicException(sprintf("Block name that will be extended is already used: '%s'.", $name));
            }

            // Associate block with extending
            $this->extends[$name] = $template;
            $this->push($template);
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
     * @return $this
     */
    protected function push($template)
    {
        $path = dirname($this->getView()->getTemplatePath($template, $this->paths));
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
            $this->pop($template);

            unset($this->extends[$name]);
            echo $this->render($template);
        } else {
            echo $this->getBlock($name);
        }
    }

    /**
     * Pop template path from stack
     * Used for relative paths in extended templates
     *
     * @param string $template Template
     *
     * @return $this
     */
    protected function pop($template)
    {
        $path = dirname($this->getView()->getTemplatePath($template, $this->paths));
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
        $this->push($template);
        $content = $this->capture($template);
        $this->pop($template);

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
     * @throws \Exception
     */
    protected function capture($template)
    {
        $file = $this->getView()
            ->getTemplatePath($template, $this->paths);
        $data = $this->getView()
            ->getData();

        try {
            extract($data, EXTR_OVERWRITE);
            ob_start();

            include $file;

            $content = ob_get_clean();
        } catch (\Exception $e) {
            ob_clean();
            throw $e;
        }

        return $content;
    }

    /**
     * Sanitize content (remove white space characters)
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
}