<?

namespace Appcia\Webwork\View;

use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\View\Renderer\Ini;
use Appcia\Webwork\View\Renderer\Json;
use Appcia\Webwork\View\Renderer\Php;
use Appcia\Webwork\View\Renderer\Xml;
use Appcia\Webwork\View\View;

/**
 * Base for view renderer
 *
 * @package Appcia\Webwork\View
 */
abstract class Renderer
{
    /**
     * Source view
     *
     * @var View
     */
    protected $view;

    /**
     * Creator
     *
     * @param mixed $data Config data
     *
     * @return $this
     */
    public static function create($data)
    {
        return Config::create($data, __CLASS__);
    }

    /**
     * Get view
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Set view
     *
     * @param View $view
     *
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Get rendered content
     *
     * @param string|null Template
     *
     * @return string
     */
    abstract public function render($template = NULL);
}