<?

namespace App\Entity\Auth;

use Appcia\Webwork\Exception;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * @Entity(repositoryClass="App\Entity\Auth\UserRepository")
 * @HasLifecycleCallbacks
 * @Table(name="auth_users")
 **/
class User
{
    const PASSWORD_SALT = '/sd][qw./qw[]$sda#2';
    const AVATAR = 'auth-user-avatar';

    const MALE = 'male';
    const FEMALE = 'female';
    const UNKNOWN = 'unknown';

    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     * @var int
     **/
    private $id;

    /**
     * @Column(type="string", unique=true)
     * @var string
     */
    private $email;

    /**
     * @Column(type="string")
     * @var string
     */
    private $nick;

    /**
     * @Column(type="string", length=40)
     * @var string
     */
    private $password;

    /**
     * @Column(type="string")
     * @var string
     **/
    private $name;

    /**
     * @Column(type="string")
     * @var string
     **/
    private $surname;

    /**
     * @Column(type="date")
     * @var string
     */
    private $birth;

    /**
     * @Column(type="string", length=16)
     * @var string
     */
    private $sex;

    /**
     * @Column(type="datetime")
     * @var \DateTime
     */
    private $registered;

    /**
     * @var Resource
     */
    private $avatar;

    /**
     * @ManyToMany(targetEntity="Group", inversedBy="users", cascade={"persist"})
     * @JoinTable(name="auth_user_groups")
     */
    private $groups;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->registered = new \DateTime();
        $this->birth = new \DateTime();
        $this->sex = self::UNKNOWN;

        $this->groups = new ArrayCollection();
    }

    /**
     * @param int $id
     *
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $nick
     *
     * @return User
     */
    public function setNick($nick)
    {
        $this->nick = $nick;

        return $this;
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return $this->nick;
    }

    /**
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $birth
     *
     * @return User
     */
    public function setBirth($birth)
    {
        if (!$birth instanceof \DateTime) {
            $birth = new \DateTime($birth);
        }

        $this->birth = $birth;

        return $this;
    }

    /**
     * @return string
     */
    public function getBirth()
    {
        return $this->birth;
    }

    /**
     * @param string $password
     * @param bool   $crypt
     *
     * @return User
     */
    public function setPassword($password, $crypt = false)
    {
        if ($crypt) {
            $password = $this->cryptPassword($password);
        }

        $this->password = $password;

        return $this;
    }

    /**
     * Generate hashed password
     *
     * @param string $password Password to be crypted
     *
     * @return string
     */
    public function cryptPassword($password = null)
    {
        if ($password === null) {
            $password = $this->password;
        }

        return sha1(md5($password . self::PASSWORD_SALT));
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $surname
     *
     * @return User
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param \DateTime $date
     *
     * @return User
     */
    public function setRegistered($date)
    {
        if (!$date instanceof \DateTime) {
            $date = new \DateTime($date);
        }

        $this->registered = $date;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRegistered()
    {
        return $this->registered;
    }

    /**
     * @param string $sex
     *
     * @return User
     * @throws Exception
     */
    public function setSex($sex)
    {
        if (!in_array($sex, $this->getSexValues())) {
            throw new Exception(sprintf("Invalid value for sex property: '%s'", $sex));
        }

        $this->sex = $sex;

        return $this;
    }

    /**
     * @return string
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * @return array
     */
    public static function getSexValues()
    {
        return array(
            self::MALE,
            self::FEMALE,
            self::UNKNOWN
        );
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->name . ' ' . $this->surname;
    }

    /**
     * @param Resource $avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @return Resource
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @PostLoad
     *
     * @param LifecycleEventArgs $args Arguments
     *
     * @return User
     */
    public function loadResources(LifecycleEventArgs $args)
    {
        $rm = $args->getObjectManager()
            ->getContainer()
            ->get('rm');

        $this->avatar = $rm->load(
            self::AVATAR,
            array('id' => $this->id)
        );

        return $this;
    }

    /**
     * @PostPersist
     * @PostUpdate
     *
     * @param LifecycleEventArgs $args Arguments
     *
     * @return User
     */
    public function saveResources(LifecycleEventArgs $args)
    {
        $config = $args->getEntityManager()
            ->getContainer()
            ->get('config');

        if (empty($this->avatar)) {
            $this->avatar = $config->get('auth.user.avatar');
        }

        $rm = $args->getObjectManager()
            ->getContainer()
            ->get('rm');

        $this->avatar = $rm->save(
            self::AVATAR,
            array('id' => $this->id),
            $this->avatar
        );

        return $this;
    }

    /**
     * @PreRemove
     *
     * @param LifecycleEventArgs $args Arguments
     *
     * @return User
     */
    public function removeResources(LifecycleEventArgs $args)
    {
        $rm = $args->getObjectManager()
            ->getContainer()
            ->get('rm');

        $rm->remove(
            self::AVATAR,
            array('id' => $this->id)
        );

        $this->avatar = null;

        return $this;
    }

    /**
     * @return array
     */
    public function getGroupIds()
    {
        $ids = array();
        foreach ($this->groups as $group) {
            $ids[] = $group->getId();
        }

        return $ids;
    }
}