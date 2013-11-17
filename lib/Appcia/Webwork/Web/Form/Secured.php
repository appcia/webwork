<?

namespace Appcia\Webwork\Web\Form;

use Appcia\Webwork\Data\Encoder;
use Appcia\Webwork\Data\Encrypter;
use Appcia\Webwork\Web\Form;
use Appcia\Webwork\Storage\Session;
use Appcia\Webwork\Web\Context;

/**
 * Secure form with CSRF protection and metadata storage
 */
class Secured extends Form
{
    /**
     * Data keys
     */
    const METADATA = 'metadata';
    const CSRF = 'csrf';

    /**
     * Data encoder
     *
     * @var Encoder
     */
    protected $encoder;

    /**
     * Token encrypter
     *
     * @var Encrypter
     */
    protected $encrypter;

    /**
     * Token session storage
     *
     * @var Session
     */
    protected $session;

    /**
     * Metadata field
     *
     * @var Field
     */
    protected $metadata;

    /**
     * Constructor
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->encoder = new Encoder();
        $this->encrypter = new Encrypter();
        $this->session = new Session();
        $this->metadata = new Field\Plain($this, self::METADATA, $this->encoder->encode(array()));
    }

    /**
     * Get data encoder
     *
     * @return Encoder
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * Set data encoder
     *
     * @param Encoder $encoder Encoder
     *
     * @return $this
     */
    public function setEncoder(Encoder $encoder)
    {
        $this->encoder = $encoder;

        return $this;
    }

    /**
     * Get token encrypter
     *
     * @return Encrypter
     */
    public function getEncrypter()
    {
        return $this->encrypter;
    }

    /**
     * Set token encrypter
     *
     * @param Encrypter $encryter
     *
     * @return $this
     */
    public function setEncrypter(Encrypter $encryter)
    {
        $this->encrypter = $encryter;

        return $this;
    }

    /**
     * Get token storage
     *
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set token storage
     *
     * @param Session $session
     *
     * @return $this
     */
    public function setSession(Session $session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Activate CSRF protection
     *
     * @param string|null $salt Encryption salt
     *
     * @return $this
     */
    public function protect($salt = null)
    {
        $token = $this->tokenize($salt);

        $this->session->set(self::CSRF, $token);
        $this->setMetadata(self::CSRF, $token);

        return $this;
    }

    /**
     * Generate token basing on field names and custom key
     *
     * @param string|null $salt Encryption salt
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function tokenize($salt = null)
    {
        if ($salt === null) {
            $salt = $this->encrypter->randSalt();
        }

        $value = implode('', array_keys($this->fields));
        $token = $this->encrypter->crypt($value, $salt);

        return $token;
    }

    /**
     * Verify CSRF protection
     *
     * @return boolean
     */
    public function verify()
    {
        $metadata = $this->getMetadata(self::CSRF);
        $session = $this->session->grab(self::CSRF);

        $flag = ($metadata == $session);

        return $flag;
    }

    /**
     * Get metadata
     *
     * @param string $key Data key
     *
     * @return mixed
     */
    public function getMetadata($key)
    {
        $data = $this->metadata->getValue();
        $metadata = $this->encoder->decode($data);

        if (!array_key_exists($key, $metadata)) {
            return null;
        }

        return $metadata[$key];
    }

    /**
     * Set metadata
     *
     * @param mixed $key   Data key
     * @param mixed $value Data value
     *
     * @return $this
     */
    public function setMetadata($key, $value)
    {
        $data = $this->metadata->getValue();
        $metadata = $this->encoder->decode($data);

        $metadata[$key] = $value;

        $data = $this->encoder->encode($metadata);
        $this->metadata->setValue($data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function suck($source, $except = array())
    {
        if (!in_array(static::METADATA, $except)) {
            $except[] = static::METADATA;
        }

        return parent::suck($source, $except);
    }

    /**
     * {@inheritdoc}
     */
    public function inject($object, $except = array())
    {
        if (!in_array(static::METADATA, $except)) {
            $except[] = static::METADATA;
        }

        return parent::inject($object, $except);
    }
}
