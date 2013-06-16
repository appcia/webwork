<?

namespace Appcia\Webwork\Web;

/**
 * Configuration related with WWW technology
 *
 * @package Appcia\Webwork\Web
 */
class Context {

    // HTML versions
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
    private $domain;

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
    private $timezone;

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
        $this->domain = 'localhost';
        $this->baseUrl = '';
        $this->locale = 'en_US';
        $this->charset = 'UTF-8';
        $this->htmlVersion = self::HTML_5;

        $this->setTimezone('Europe/Warsaw');
        $this->updateLocale();
    }

    /**
     * @return $this
     */
    private function updateLocale()
    {
        $locale =  $this->locale . '.' . strtoupper($this->charset);

        putenv('LC_ALL=' . $locale);
        setlocale(LC_ALL, $locale);

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
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        $this->updateLocale();

        return $this;
    }

    /**
     * @param string $timezone
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setTimezone($timezone)
    {
        $flag = date_default_timezone_set($timezone);
        if (!$flag) {
            throw new \InvalidArgumentException(sprintf("Timezone '%s' is invalid or unsupported.", $timezone));
        }

        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
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
     * @return $this
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
     * @return $this
     */
    public function setHtmlVersion($htmlVersion)
    {
        $this->htmlVersion = $htmlVersion;

        return $this;
    }

}