<?

namespace Appcia\Webwork\View;

use Appcia\Webwork\View;

abstract class Helper
{
    /**
     * @var View
     */
    private $view;

    /**
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
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }
}