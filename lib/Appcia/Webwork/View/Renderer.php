<?

namespace Appcia\Webwork\View;

use Appcia\Webwork\Core\Object;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\View\View;

/**
 * Base for view renderer
 *
 * @package Appcia\Webwork\View
 */
abstract class Renderer extends Object
{
    /**
     * Source view
     *
     * @var View
     */
    protected $view;

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