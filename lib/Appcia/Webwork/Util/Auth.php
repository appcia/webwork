<?

namespace Appcia\Webwork\Util;

use Appcia\Webwork\Session;

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
     * @var Object
     */
    private $user;

    /**
     * @var string
     */
    private $token;

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

            $this->user = $data['user'];
            $this->token = $data['token'];
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
            'user' => $this->user,
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
        return $this->user !== null;
    }

    /**
     * @param Object $user
     *
     * @return Auth
     */
    public function authorize($user) {
        $this->user = $user;
        $this->token = sha1(md5('dkl3f$')); // @todo temporary token

        $this->save();

        return $this;
    }

    /**
     * @return Auth
     */
    public function unauthorize() {
        $this->user = null;
        $this->token = null;

        $this->save();

        return $this;
    }

    /**
     * @return Object
     * @throws \ErrorException
     */
    public function getUser() {
        if (!$this->isAuthorized()) {
            throw new \ErrorException('Cannot get user when access is unauthorized');
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

}