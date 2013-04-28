<?

namespace App\Entity\Auth;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @param array $ids
     * @return array
     */
    public function findById(array $ids) {
        if (empty($ids)) {
            return array();
        }

        $query = $this->createQueryBuilder('u')
            ->where('u.id IN (:id)')
            ->setParameter('id', $ids)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @return array
     */
    public function findAllOrderByName() {
        $query = $this->createQueryBuilder('u')
            ->orderBy('u.name', 'asc')
            ->getQuery();

        return $query->getResult();
    }
}