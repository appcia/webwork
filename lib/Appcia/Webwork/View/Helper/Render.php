<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Render extends Helper
{
    /**
     * Caller
     *
     * @param string $template Template to be rendered
     *
     * @return string
     */
    public function render($template)
    {
        $content = $this->getView()
            ->getRenderer()
            ->render($template);

        return $content;
    }
}
