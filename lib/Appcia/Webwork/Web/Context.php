<?

namespace Appcia\Webwork\Web;

use Appcia\Webwork\Intl\Translator;
use Appcia\Webwork\Intl\Locale;

/**
 * Configuration related with WWW technology
 *
 * @package Appcia\Webwork\Web
 */
class Context {

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
     * Character encoding
     *
     * @var string
     */
    protected $charset;

    /**
     * HTML language version
     *
     * @var string
     */
    protected $htmlVersion;

    /**
     * @var Locale
     */
    protected $locale;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->domain = 'localhost';
        $this->baseUrl = '';
        $this->charset = 'UTF-8';
        $this->htmlVersion = self::HTML_5;
        $this->locale = new Locale($this);
        $this->translator = new Translator\Php($this);
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
        $this->locale->update();

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

    /**
     * @param Locale $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        if (!$locale instanceof Locale) {
            $locale = Locale::create($locale, array($this));
        }

        $this->locale = $locale;

        return $this;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param Translator $translator
     *
     * @return $this
     */
    public function setTranslator($translator)
    {
        if (!$translator instanceof Translator) {
            $translator = Translator::create($translator, array($this));
        }

        $this->translator = $translator;

        return $this;
    }

    /**
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }
}