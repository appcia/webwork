<?

namespace App\DataFixture\Auth;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Auth\User;

class UserFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder() {
        return 10;
    }
    
    public function load(ObjectManager $manager)
    {
        $appcia = new User();
        $appcia->setNick('appcia')
            ->setEmail('appcia.dev@gmail.com')
            ->setPassword('qwa2_pp2op2', true)
            ->setName('Appcia')
            ->setSurname('Development')
            ->setBirth(new \DateTime('21-05-1991'))
            ->setSex(User::MALE)
            ->getGroups()->add($this->getReference('auth_group_admin'));

        $manager->persist($appcia);
        $this->addReference('auth_user_appcia', $appcia);

        $foo = new User();
        $foo->setNick('foo')
            ->setEmail('foo.bar@dot.com')
            ->setPassword('qwa2_pp2op2', true)
            ->setName('Foo')
            ->setSurname('Bar')
            ->setSex(User::FEMALE)
            ->setBirth(new \DateTime('01-12-1987'))
            ->getGroups()->add($this->getReference('auth_group_user'));

        $manager->persist($foo);
        $this->addReference('auth_user_foo', $foo);
        
        $manager->flush();
    }
}