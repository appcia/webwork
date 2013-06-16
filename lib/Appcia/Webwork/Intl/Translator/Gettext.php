<?

namespace Appcia\Webwork\Intl\Translator;

use Appcia\Webwork\Intl\Translator;
use Appcia\Webwork\Web\Context;

/**
 * Gettext module translator
 *
 * @package Appcia\Webwork\Intl\Translator
 */
class Gettext extends Translator
{
    /**
     * @var array
     */
    private $domain;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        if (function_exists('gettext') === false) {
            throw new \RuntimeException("Translator cannot be instantiated. Gettext is not installed.");
        }

        $this->setDomain(array(
            'name' => 'messages',
            'path' => 'locale'
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

        bindtextdomain($domain['name'], $domain['path']);
        textdomain($domain['name']);

        $this->domain = $domain;

        return $this;
    }

}