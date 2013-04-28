<?

namespace App\Entity\Auth;

use App\Entity\Auth\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * @Entity(repositoryClass="App\Entity\Auth\GroupRepository")
 * @HasLifecycleCallbacks
 * @Table(name="auth_groups")
 */
class Group {
    const ICON = 'auth-group-icon';

    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     * @var int
     **/
    private $id;

    /**
     * @Column(type="string")
     * @var string
     **/
    private $name;

    /**
     * @Column(type="string")
     * @var string
     **/
    private $description;

    /**
     * @ManyToMany(targetEntity="User", mappedBy="groups", cascade={"persist"})
     * @var ArrayCollection
     */
    private $users;

    /**
     * @Column(type="datetime")
     * @var \DateTime
     */
    private $created;

    /**
     * @var Resource
     */
    private $icon;

    /**
     * Constructor
     */
    public function __construct() {
        $this->users = new ArrayCollection();
        $this->created = new \DateTime();
    }

    /**
     * @param int $id
     *
     * @return Group
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return Group
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
     * @param string $description
     *
     * @return Group
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param \DateTime $created
     *
     * @return Group
     */
    public function setCreated($created)
    {
        if (!$created instanceof \DateTime) {
            $created = new \DateTime($created);
        }

        $this->created = $created;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param Resource $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @return Resource
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers() {
        return $this->users;
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

        $this->icon = $rm->load(
            self::ICON,
            array('id' => $this->id)
        );

        return $this;
    }

    /**
     * @PostPersist
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

        if (empty($this->icon)) {
            $this->icon = $config->get('auth.group.avatar');
        }

        $rm = $args->getObjectManager()
            ->getContainer()
            ->get('rm');

        $this->icon = $rm->save(
            self::ICON,
            array('id' => $this->id),
            $this->icon
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
            self::ICON,
            array('id' => $this->id)
        );

        $this->icon = null;

        return $this;
    }
}