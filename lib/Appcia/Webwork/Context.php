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
        $this->charset = 'UTF-8';
        $this->htmlVersion = self::HTML_5;
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
     * @param string $charset
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
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