<?

namespace Appcia\Webwork\Util;

use Appcia\Webwork\Session;
use Appcia\Webwork\Exception;

class Auth
{
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
     * Constructor
     *
     * @param Session $session
     * @param string $namespace
     */
    public function __construct(Session $session, $namespace = 'auth')
    {
        $this->session = $session;
        $this->namespace = $namespace;

        $this->load();
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
            'token' => $this->token
        );

        $this->session->set($this->namespace, $data);

        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthorized()
    {
        return $this->userData !== null;
    }

    /**
     * When inherited, could unserialize user after loading
     *
     * @param $user
     * @return mixed
     */
    protected function wakeupUser($user) {
        return $user;
    }

    /**
     * When inherited, could serialize user before saving
     *
     * @param string $user
     *
     * @return object
     */
    protected function sleepUser($user) {
        return $user;
    }

    /**
     * @return Object
     * @throws Exception
     */
    public function getUser() {
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
     * @param string $value Value to be tokenized
     *
     * @return string
     */
    public function generateToken($value) {
        return sha1(md5($value));
    }

    /**
     * @param Object $user
     *
     * @return Auth
     */
    public function authorize($user) {
        $this->userData = $this->sleepUser($user);
        $this->token = $this->generateToken('32kj43@#132_14'); // @todo improve token security

        $this->save();

        return $this;
    }

    /**
     * @return Auth
     */
    public function unauthorize() {
        $this->userData = null;
        $this->token = null;

        $this->user = null;

        $this->save();

        return $this;
    }

}