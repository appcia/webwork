<?

namespace Appcia\Webwork\Auth;

use App\Entity\Auth\User;
use Appcia\Webwork\Exception\Exception;
use Appcia\Webwork\Routing\Route;
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
    private $data;

    /**
     * Authorized user
     *
     * @var object
     */
    private $user;

    /**
     * Token salt
     *
     * @var string
     */
    private $salt;

    /**
     * Expiration time
     *
     * @var int|null
     */
    private $expirationTime;

    /**
     * Constructor
     *
     * @param Space $space Data storage
     */
    public function __construct(Space $space)
    {
        $space->setAutoflush(true);
        $this->data = $space;
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
     * @return Auth
     */
    public function setExpirationTime($expirationTime)
    {
        $this->expirationTime = $expirationTime;

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
            $user = $this->wakeupUser($this->data['userData']);
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
     * @return bool
     */
    public function isAuthorized()
    {
        if (empty($this->data['userData'])) {
            return false;
        }

        $token = $this->generateToken();

        if ($this->data['token'] != $token) {
            return false;
        }

        if ($this->expirationTime !== null) {
            $time = time();
            $delayTime = $time - $this->data['time'];

            if ($delayTime > $this->expirationTime) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate authorization token
     *
     * @return string
     */
    public function generateToken()
    {
        $token = sha1(md5($this->salt . $this->data['userData']));

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
     * @return Auth
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @param Object $user
     *
     * @return Auth
     */
    public function authorize($user)
    {
        $this->data['userData'] = $this->sleepUser($user);
        $this->data['token'] = $this->generateToken();
        $this->data['time'] = time();

        return $this;
    }

    /**
     * Encode user value to be stored in session
     *
     * @param string $user User
     *
     * @return object
     */
    protected function sleepUser($user)
    {
        return $user;
    }

    /**
     * Dispose authorization data
     *
     * @return Auth
     */
    public function unauthorize()
    {
        $this->data['userData'] = null;
        $this->data['token'] = null;

        $this->user = null;

        return $this;
    }
}