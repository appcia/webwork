<?

namespace Appcia\Webwork\Intl;

use Appcia\Webwork\Core\Object;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\Web\Context;

/**
 * Locale representation
 *
 * @package Appcia\Webwork\Intl
 */
class Locale extends Object
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * Current locale
     *
     * @var string
     */
    protected $active;

    /**
     * Available localizations
     *
     * @var array
     */
    protected $list;

    /**
     * Current timezone
     *
     * @var string
     */
    protected $timezone;

    /**
     * Constructor
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;

        $this->active = 'en_US';
        $this->list = array('en_US');

        $this->setTimezone('Europe/Warsaw');
        $this->update();
    }

    /**
     * @return $this
     */
    public function update()
    {
        $locale =  $this->active . '.' . strtoupper($this->context->getCharset());

        putenv('LC_ALL=' . $locale);
        setlocale(LC_ALL, $locale);

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
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param Context $context
     *
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param string $locale
     *
     * @return $this
     */
    public function setActive($locale)
    {
        $this->active = $locale;
        $this->update();

        return $this;
    }

    /**
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param array $locales
     *
     *
     * @return $this
     */
    public function setList($locales)
    {
        $this->list = (array) $locales;

        return $this;
    }
}