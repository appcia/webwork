<?

namespace Appcia\Webwork\View;

use Appcia\Webwork\View;
use Appcia\Webwork\Component;

abstract class Helper extends Component
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