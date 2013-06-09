<?

namespace Appcia\Webwork\View;

use Appcia\Webwork\View\View;
use Appcia\Webwork\Core\Component;

/**
 * Base for view helper (PHP renderer tool)
 *
 * @package Appcia\Webwork\View
 */
abstract class Helper extends Component
{
    /**
     * Attached view
     *
     * @var View
     */
    private $view;

    /**
     * Set attached view
     *
     * @param View $view
     *
     * @return $this
     */
    public function setView(View $view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Get attached view
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Get another helper
     *
     * @param string $name
     *
     * @return Helper
     */
    public function getHelper($name)
    {
        return $this->getView()
            ->getRenderer()
            ->getHelper($name);
    }
}