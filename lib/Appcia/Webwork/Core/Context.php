<?

namespace Appcia\Webwork\Core;

use Appcia\Webwork\Intl\Locale;
use Appcia\Webwork\Intl\Translator;

/**
 * Context configuration
 */
class Context
{
    /**
     * Character encoding
     *
     * @var string
     */
    protected $charset;

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
        $this->charset = 'UTF-8';
        $this->locale = new Locale($this);
        $this->translator = new Translator\Php($this);
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