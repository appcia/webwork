<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Asset extends Helper
{
    /**
     * Registered assets
     *
     * @var array
     */
    private $assets;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->assets = array();
    }

    /**
     * Caller
     *
     * @param string $file Asset resource to be registered
     *
     * @return string
     */
    public function asset($file)
    {
        $base = trim($this
            ->getView()
            ->getSetting('baseUrl'), '/');

        $file = trim($file, '/');

        // Register
        if (!array_search($file, $this->assets)) {
            $this->assets[] = $file;
        }

        // Generate proper url
        $url = 'public';
        if (!empty($base)) {
            $url .= '/' . $base;
        }
        $url .= '/' . $file;

        return $url;
    }

    /**
     * @return array
     */
    public function getAssets()
    {
        return $this->assets;
    }
}
