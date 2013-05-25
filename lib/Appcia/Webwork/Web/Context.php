<?

namespace Appcia\Webwork\Web;

/**
 * Configuration related with WWW technology
 *
 * @package Appcia\Webwork\Web
 */
class Context {

    const HTML_5 = 'HTML 5';
    const HTML_401 = 'HTML 4.01';

    /**
     * @var array
     */
    private static $htmlVersions = array(
        self::HTML_5,
        self::HTML_401
    );

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $textDomain;

    /**
     * @var string
     */
    private $charset;

    /**
     * @var string
     */
    private $htmlVersion;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->baseUrl = '';
        $this->locale = 'en_US';
        $this->charset = 'UTF-8';
        $this->htmlVersion = self::HTML_5;

        $this->updateLocale();
    }

    /**
     * @return Context
     */
    private function updateLocale()
    {
        $locale =  $this->locale . '.' . strtoupper($this->charset);

        putenv('LC_ALL=' . $locale);
        setlocale(LC_ALL, $locale);

        $domain = "messages";
        bindtextdomain($domain, "./locale");
        bind_textdomain_codeset($domain, 'UTF-8');
        textdomain($domain);

        return $this;
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
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Set base URL
     *
     * @param string $baseUrl
     *
     * @return Context
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return Context
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        $this->updateLocale();

        return $this;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     *
     * @return Context
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
        $this->updateLocale();

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
     * @return Context
     */
    public function setHtmlVersion($htmlVersion)
    {
        $this->htmlVersion = $htmlVersion;

        return $this;
    }

}