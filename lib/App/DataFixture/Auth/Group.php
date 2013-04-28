<?

namespace App\DataFixture\Auth;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Auth\Group;

class GroupFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 1;
    }

    public function load(ObjectManager $manager)
    {
        $admin = new Group();
        $admin->setName('Administrators')
            ->setDescription('Have full access to system');

        $manager->persist($admin);
        $this->setReference('auth_group_admin', $admin);

        $user = new Group();
        $user->setName('Regular users')
            ->setDescription('Can view and modify contents');

        $manager->persist($user);
        $this->setReference('auth_group_user', $user);

        $manager->flush();

    }
}