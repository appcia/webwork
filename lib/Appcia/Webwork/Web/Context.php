<?

namespace Appcia\Webwork\Web;

use Appcia\Webwork\Core\Context as Base;

/**
 * Context configuration related with WWW technology
 */
class Context extends Base {

    /**
     * HTML versions
     */
    const HTML_5 = 'HTML 5';
    const HTML_401 = 'HTML 4.01';

    /**
     * Available HTML versions
     *
     * @var array
     */
    protected static $htmlVersions = array(
        self::HTML_5,
        self::HTML_401
    );

    /**
     * Domain name
     *
     * @var string
     */
    protected $domain;

    /**
     * URL prefix after domain
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * HTML language version
     *
     * @var string
     */
    protected $htmlVersion;

    /**
     * Constructor
     */
    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->domain = 'localhost';
        $this->baseUrl = '';
        $this->htmlVersion = self::HTML_5;
    }

    /**
     * @return array
     */
    public static function getHtmlVersions()
    {
        return self::$htmlVersions;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     *
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Set base URL
     *
     * @param string $baseUrl
     *
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getHtmlVersion()
    {
        return $this->htmlVersion;
    }

    /**
     * @param string $htmlVersion
     *
     * @return $this
     */
    public function setHtmlVersion($htmlVersion)
    {
        $this->htmlVersion = $htmlVersion;

        return $this;
    }
}