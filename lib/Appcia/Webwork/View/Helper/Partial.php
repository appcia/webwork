<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;
use Appcia\WebworkView;

class Partial extends Helper
{
    /**
     * Caller
     *
     * @param string $file File to be rendered
     * @param array  $data Data for partial
     *
     * @return string
     */
    public function partial($file, array $data = array())
    {
        $container = $this
            ->getView()
            ->getContainer();

        $view = new View($container);
        $view->setFile($file)
            ->setData($data);

        return $view->render();
    }
}
