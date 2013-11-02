<?

namespace Appcia\Webwork\View;

use Appcia\Webwork\Core\Object;
use Appcia\Webwork\Core\Objector;

/**
 * Base for view renderer
 *
 * @package Appcia\Webwork\View
 */
abstract class Renderer implements Object
{
    /**
     * Source view
     *
     * @var View
     */
    protected $view;

    /**
     * {@inheritdoc}
     */
    public static function objectify($data, $args = array())
    {
        return Objector::objectify($data, $args, get_called_class());
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