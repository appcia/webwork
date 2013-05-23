<?

namespace Appcia\Webwork\View\Renderer;

use Appcia\Webwork\Exception\Exception;
use Appcia\Webwork\View\Helper;
use Appcia\Webwork\View\Renderer;

class Php extends Renderer
{
    /**
     * Registered helpers
     *
     * @var array
     */
    private $helpers;

    /**
     * Output compression
     *
     * @var bool
     */
    private $sanitization;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->helpers = array();
        $this->sanitization = false;
    }

    /**
     * {@inheritdoc}
     */
    public function render($template = null)
    {
        $content = $this->capture($template);

        if ($this->sanitization) {
            $content = $this->sanitize($content);
        }

        return $content;
    }

    /**
     * Call view helper using $this->{helperName}({args...}) in templates
     *
     * @param $name Helper name
     * @param $args Helper arguments
     *
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $args)
    {
        $helper = $this->getHelper($name);

        $method = mb_strtolower($name);
        $callback = array($helper, $method);

        if (!is_callable($callback)) {
            throw new Exception(sprintf("View helper '%s' does not have accessible method: '%s", $name, $method));
        }

        $result = call_user_func_array($callback, $args);

        return $result;
    }

    /**
     * Create helper by name
     * Search for valid class name in all modules
     *
     * @param string $name Name
     *
     * @return mixed
     * @throws Exception
     */
    private function createHelper($name)
    {
        $class = 'Appcia\\Webwork\\View\\Helper\\' . ucfirst($name);

        if (class_exists($class)) {
            return new $class();
        }

        $modules = $this->getView()
            ->getContainer()
            ->get('bootstrap')
            ->getModules();

        foreach ($modules as $module) {
            $class = $module->getNamespace() . '\\View\\Helper\\' . ucfirst($name);

            if (class_exists($class)) {
                return new $class();
            }
        }

        throw new Exception(sprintf("View helper '%s' cannot be created. There is no valid class in any module", $class));
    }

    /**
     * Get helper by name
     *
     * @param string $name Name
     *
     * @return Helper
     * @throws Exception
     */
    public function getHelper($name)
    {
        if (!isset($this->helpers[$name])) {
            $context = $this->getView()
                ->getContainer()
                ->get('context');

            $helper = $this->createHelper($name);
            $helper->setView($this->getView())
                ->setContext($context);

            $this->helpers[$name] = $helper;
        }

        return $this->helpers[$name];
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
     * @param bool $sanitization Flag
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
     * @return bool
     */
    public function isSanitization()
    {
        return $this->sanitization;
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

    /**
     * Capture included file
     *
     * @param string $template Template
     *
     * @return string
     * @throws Exception
     */
    protected function capture($template)
    {
        $file = $this->getView()
            ->getTemplatePath($template);
        $data = $this->getView()
            ->getData();

        extract($data);
        ob_start();

        if ((@include $file) === false) {
            throw new Exception(sprintf("Template file cannot be included properly: '%s'", $file));
        }

        $content = ob_get_clean();

        return $content;
    }
}