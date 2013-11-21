<?

namespace Appcia\Webwork\Intl\Translator;

use Appcia\Webwork\Intl\Translator;
use Appcia\Webwork\Web\Context;

/**
 * Gettext module translator
 */
class Gettext extends Translator
{
    /**
     * @var array
     */
    protected $domain;

    /**
     * Constructor
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);

        if (function_exists('gettext') === false) {
            throw new \RuntimeException("Translator cannot be instantiated. Gettext is not installed.");
        }

        $this->setDomain(array(
            'name' => 'messages',
            'path' => 'locale',
            'invalidate' => null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function translate($id)
    {
        $text = gettext($id);

        return $text;
    }

    /**
     * @return array
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param array $domain
     *
     * @link http://www.php.net/manual/en/function.gettext.php#110735
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setDomain(array $domain)
    {
        if (empty($domain['name'])) {
            throw new \InvalidArgumentException('Gettext domain name not specified');
        }

        if (!isset($domain['path'])) {
            throw new \InvalidArgumentException('Gettext domain path not specified');
        }

        if (isset($domain['invalidate'])) {
            bindtextdomain($domain['name'], $domain['invalidate']);
        }

        bindtextdomain($domain['name'], $domain['path']);
        textdomain($domain['name']);

        $this->domain = $domain;

        return $this;
    }

}