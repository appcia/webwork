<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;
use Appcia\Webwork\View\View;

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
        $app = $this->getView()
            ->getApp();

        $view = new View($app);

        $content = $view->setTemplate($file)
            ->setData($data)
            ->render();

        return $content;
    }
}
