<?

namespace Appcia\Webwork;

class Context {

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
    private $charset;

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
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
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
    public function getLocale()
    {
        return $this->locale;
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
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @return Context
     */
    private function updateLocale()
    {
        $locale =  $this->locale . '.' . strtoupper($this->charset);

        putenv('LC_ALL=' . $locale);
        setlocale(LC_ALL, $locale);

        return $this;
    }

    /**
     * @param string $htmlVersion
     */
    public function setHtmlVersion($htmlVersion)
    {
        $this->htmlVersion = $htmlVersion;
    }

    /**
     * @return string
     */
    public function getHtmlVersion()
    {
        return $this->htmlVersion;
    }

    /**
     * @return array
     */
    public static function getHtmlVersions()
    {
        return self::$htmlVersions;
    }

}