<?

namespace Appcia\Webwork\Util;

use Appcia\Webwork\Session;
use Appcia\Webwork\Exception;

class Auth
{
    const TOKEN_SALT = '32kj43@#132_14';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $userData;

    /**
     * @var string
     */
    private $token;

    /**
     * @var Object
     */
    private $user;

    /**
     * @var int|null
     */
    private $expirationTime;

    /**
     * Constructor
     *
     * @param Session $session   Session object
     * @param string  $namespace Session namespace
     */
    public function __construct(Session $session, $namespace = 'auth')
    {
        $this->session = $session;
        $this->namespace = $namespace;
        $this->expirationTime = null;

        $this->load();
    }

    /**
     * @param int|null $expirationTime
     */
    public function setExpirationTime($expirationTime)
    {
        $this->expirationTime = $expirationTime;
    }

    /**
     * @return int|null
     */
    public function getExpirationTime()
    {
        return $this->expirationTime;
    }

    /**
     * Load messages from storage
     *
     * @return Flash
     */
    private function load()
    {
        if ($this->session->has($this->namespace)) {
            $data = $this->session->get($this->namespace);

            $this->userData = isset($data['userData']) ? $data['userData'] : null;
            $this->token = isset($data['token']) ? $data['token'] : null;
            $this->time = isset($data['time']) ? $data['time'] : null;
        }

        return $this;
    }

    /**
     * Save auth in storage
     *
     * @return Flash
     */
    private function save()
    {
        $data = array(
            'userData' => $this->userData,
            'token' => $this->token,
            'time' => $this->time,
        );

        $this->session->set($this->namespace, $data);

        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthorized()
    {
        if (empty($this->userData)) {
            return false;
        }

        $token = $this->generateToken();
        if ($this->token != $token) {
            return false;
        }

        if ($this->expirationTime !== null) {
            $time = time();
            $delayTime = $time - $this->time;

            if ($delayTime > $this->expirationTime) {
                return false;
            }
        }

        return true;
    }

    /**
     * When inherited, could unserialize user after loading
     *
     * @param $user
     *
     * @return mixed
     */
    protected function wakeupUser($user)
    {
        return $user;
    }

    /**
     * When inherited, could serialize user before saving
     *
     * @param string $user
     *
     * @return object
     */
    protected function sleepUser($user)
    {
        return $user;
    }

    /**
     * @return Object
     * @throws Exception
     */
    public function getUser()
    {
        if (!$this->isAuthorized()) {
            throw new Exception('Cannot get user when access is unauthorized');
        }

        if ($this->user === null) {
            $this->user = $this->wakeupUser($this->userData);
        }

        return $this->user;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function generateToken()
    {
        return sha1(md5(self::TOKEN_SALT . $this->userData));
    }

    /**
     * @param Object $user
     *
     * @return Auth
     */
    public function authorize($user)
    {
        $this->userData = $this->sleepUser($user);
        $this->token = $this->generateToken();
        $this->time = time();

        $this->save();

        return $this;
    }

    /**
     * @return Auth
     */
    public function unauthorize()
    {
        $this->userData = null;
        $this->token = null;
        $this->user = null;

        $this->save();

        return $this;
    }

}