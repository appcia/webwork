<?

namespace Appcia\Webwork\View;

use Appcia\Webwork\View;
use Appcia\Webwork\Component;

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
     * @return Helper
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