<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\Data\Value;
use Appcia\Webwork\View\Helper;

class Asset extends Helper
{
    /**
     * Registered assets
     *
     * @var array
     */
    protected $assets;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

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
        if (Value::isEmpty($file)) {
            return null;
        }

        // Register
        $file = trim($file, '/');
        if (!array_search($file, $this->assets)) {
            $this->assets[] = $file;
        }

        // Generate proper url
        $url = $this->getContext()
            ->getBaseUrl();

        if (!empty($url)) {
            $url .= '/';
        }

        $url .= 'public/' . $file;

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
