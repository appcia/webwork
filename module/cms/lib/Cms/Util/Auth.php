<?

namespace Cms\Util;

use Doctrine\ORM\EntityManager;
use Appcia\Webwork\Session;
use Appcia\Webwork\Util\Auth as UtilAuth;
use App\Entity\Auth\User;

class Auth extends UtilAuth {

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor
     *
     * @param EntityManager $em        Entity manager
     * @param Session       $session   Session
     * @param string        $namespace Namespace
     * @param string        $key       Variable token key
     */
    public function __construct(EntityManager $em, Session $session, $namespace = 'auth', $key = null)
    {
        $this->em = $em;

        parent::__construct($session, $namespace, $key);
    }

    /**
     * Store ID in session
     *
     * @param User $user
     *
     * @return int
     */
    protected function sleepUser($user) {
        $id = $user->getId();

        return $id;
    }

    /**
     * Load user basing on ID from session
     *
     * @param int $userId User ID
     *
     * @return User
     */
    protected function wakeupUser($userId) {
        $user = $this->em->find('App\Entity\Auth\User', $userId);

        return $user;
    }

}