<?

namespace Appcia\Webwork\Auth;

use Appcia\Webwork\Data\Encoder;
use Appcia\Webwork\Data\Encrypter;
use Appcia\Webwork\Storage\Session\Space;

/**
 * Provides basic authorization
 * User data stored in session space for specified time
 *
 * @package Appcia\Webwork\Auth
 */
class Auth
{
    /**
     * Data storage
     *
     * @var Space
     */
    protected $data;

    /**
     * Authorized user
     *
     * @var object
     */
    protected $user;

    /**
     * Token salt
     *
     * @var string
     */
    protected $salt;

    /**
     * Expiration time
     *
     * @var int|null
     */
    protected $expirationTime;

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
     * Constructor
     *
     * @param Space $space Data storage
     */
    public function __construct(Space $space)
    {
        $space->setAutoflush(true);
        $this->data = $space;

        $this->encoder = new Encoder();
        $this->encrypter = new Encrypter();
    }

    /**
     * Get data storage
     *
     * @return Space
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get expiration time
     *
     * @return int|null
     */
    public function getExpirationTime()
    {
        return $this->expirationTime;
    }

    /**
     * Set expiration time
     *
     * @param int|null $expirationTime
     *
     * @return $this
     */
    public function setExpirationTime($expirationTime)
    {
        $this->expirationTime = $expirationTime;

        return $this;
    }

    /**
     * @return Encoder
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * @param Encoder $encoder
     *
     * @return $this
     */
    public function setEncoder($encoder)
    {
        $this->encoder = $encoder;

        return $this;
    }

    /**
     * @return Encrypter
     */
    public function getEncrypter()
    {
        return $this->encrypter;
    }

    /**
     * @param Encrypter $encrypter
     *
     * @return $this
     */
    public function setEncrypter($encrypter)
    {
        $this->encrypter = $encrypter;

        return $this;
    }

    /**
     * Get authorized user
     *
     * @return object
     * @throws \LogicException
     * @throws \ErrorException
     */
    public function getUser()
    {
        if (!$this->isAuthorized()) {
            throw new \LogicException('Auth failed. User is unauthorized.');
        }

        if ($this->user === null) {
            $user = $this->wakeupUser($this->data['user']);

            if ($user === null) {
                throw new \ErrorException('Auth failed. User is not available.');
            }

            $this->user = $user;
        }

        return $this->user;
    }

    /**
     * Check whether user is authorized
     *
     * @return boolean
     */
    public function isAuthorized()
    {
        if (!$this->isUserData() || !$this->isTokenValid() || $this->isExpired()) {
            return false;
        }

        return true;
    }

    /**
     * Check whether user data exists
     *
     * @return bool
     */
    public function isUserData()
    {
        $exists = !empty($this->data['user']);

        return $exists;
    }

    /**
     * Check whether authorization data is touched
     *
     * @return bool
     */
    public function isTokenValid()
    {
        if (empty($this->data['token'])) {
            return false;
        }

        $token = $this->generateToken();
        $valid = ($this->data['token'] == $token);

        return $valid;
    }

    /**
     * CHeck whether expiration time exceeded
     *
     * @return bool
     */
    public function isExpired()
    {
        if ($this->expirationTime !== null) {
            if (empty($this->data['time'])) {
                return true;
            }

            $time = time();
            $delayTime = ($time - $this->data['time']);

            if ($delayTime > $this->expirationTime) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate authorization token
     *
     * @return string
     */
    public function generateToken()
    {
        $token = $this->encrypter->setSalt($this->salt)
            ->crypt($this->data['user']);

        return $token;
    }

    /**
     * Decode user value stored in session
     *
     * @param mixed $user User data
     *
     * @return mixed
     */
    protected function wakeupUser($user)
    {
        $user = $this->encoder->decode($user);

        return $user;
    }

    /**
     * Get token salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set token salt
     *
     * @param string $salt
     *
     * @return $this
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @param mixed $user
     *
     * @return $this
     */
    public function authorize($user)
    {
        $this->data['user'] = $this->sleepUser($user);
        $this->data['token'] = $this->generateToken();
        $this->data['time'] = time();

        return $this;
    }

    /**
     * Encode user value to be stored in session
     *
     * @param mixed $user
     *
     * @return string
     */
    protected function sleepUser($user)
    {
        $data = $this->encoder->encode($user);

        return $data;
    }

    /**
     * Dispose authorization data
     *
     * @return $this
     */
    public function unauthorize()
    {
        unset($this->data['user']);
        unset($this->data['token']);
        unset($this->data['time']);

        $this->user = null;

        return $this;
    }
}