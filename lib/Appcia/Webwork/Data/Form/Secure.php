<?

namespace Appcia\Webwork\Data\Form;

use Appcia\Webwork\Data\Encoder;
use Appcia\Webwork\Data\Encrypter;
use Appcia\Webwork\Data\Form;
use Appcia\Webwork\Storage\Session;
use Appcia\Webwork\Web\Context;

/**
 * Secure form with CSRF protection and metadata storage
 */
class Secure extends Form
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
    protected $encryter;

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
        $this->encoder = new Encoder();
        $this->encryter = new Encrypter();
        $this->session = new Session();
        $this->metadata = new Field\Plain(self::METADATA, $this->encoder->encode(array()));

        parent::__construct($context);
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
    public function setEncoder($encoder)
    {
        $this->encoder = $encoder;

        return $this;
    }

    /**
     * Get token encrypter
     *
     * @return Encrypter
     */
    public function getEncryter()
    {
        return $this->encryter;
    }

    /**
     * Set token encrypter
     *
     * @param Encrypter $encryter
     *
     * @return $this
     */
    public function setEncryter($encryter)
    {
        $this->encryter = $encryter;

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
    public function setSession($session)
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
        if ($salt !== null && !is_string($salt) && !is_numeric($salt)) {
            throw new \InvalidArgumentException('Form token key should be a number or a string.');
        }

        $value = implode('', array_keys($this->fields));
        $token = $this->encryter->crypt($value, $salt);

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
    public function populate(array $data)
    {
        parent::populate($data);

        if (isset($data[self::METADATA])) {
            $this->metadata->setValue($data[self::METADATA]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getField($name)
    {
        if ($name === self::METADATA) {
            return $this->metadata;
        }

        return parent::getField($name);
    }
}