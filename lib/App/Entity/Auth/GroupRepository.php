<?

namespace App\Entity\Auth;

use Doctrine\ORM\EntityRepository;

class GroupRepository extends EntityRepository
{
    /**
     * @param array $ids
     * @return array
     */
    public function findById(array $ids) {
        if (empty($ids)) {
            return array();
        }

        $query = $this->createQueryBuilder('g')
            ->where('g.id IN (:id)')
            ->setParameter('id', $ids)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @return array
     */
    public function findAllOrderByName() {
        $query = $this->createQueryBuilder('g')
            ->orderBy('g.name', 'asc')
            ->getQuery();

        return $query->getResult();
    }
}