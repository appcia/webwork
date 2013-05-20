<?

namespace Appcia\Webwork\View;

use Appcia\Webwork\View;

abstract class Renderer
{
    /**
     * Source view
     *
     * @var View
     */
    private $view;

    /**
     * Set view
     *
     * @param View $view
     *
     * @return Renderer
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
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
     * Get rendered content
     *
     * @param string|null Template
     *
     * @return string
     */
    abstract public function render($template = null);
}