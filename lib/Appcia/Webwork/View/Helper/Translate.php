<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Translate extends Helper
{
    /**
     * Caller
     *
     * @param string $id Message ID
     *
     * @return string
     */
    public function translate($id)
    {
        $id = $this->getStringValue($id);
        if ($id === null) {
            return null;
        }

        $translator = $this->getView()
            ->getApp()
            ->get('translator');

        $message = $translator->translate($id);

        return $message;
    }
}
