<?

namespace Appcia\Webwork\View;

use Appcia\Webwork\Data\Component;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\View\View;

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
    protected $view;

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
}