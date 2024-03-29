<?php

namespace Nsm\Bundle\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Nsm\Bundle\CoreBundle\Entity\QueryBuilderInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * AbstractRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AbstractRepository extends EntityRepository implements RepositoryInterface
{

    /**
     * @param $className
     *
     * @return mixed
     */
    private function getEntityAlias($className)
    {
        $className = explode('\\', $className);

        return end($className);
    }

    /**
     * @param null $alias
     * @param null $indexBy
     *
     * @return AbstractQueryBuilder
     */
    public function createQueryBuilder($alias = null, $indexBy = null)
    {
        if(null === $alias) {
            $alias = $this->getEntityAlias($this->getClassName());
        }

        $class = str_replace("Repository", "QueryBuilder", get_called_class());

        /** @var AbstractQueryBuilder $qb */
        $qb = new $class($this->getEntityManager(), $this);
        $qb->select($alias)
            ->from($this->_entityName, $alias, $indexBy);

        return $qb;
    }

    /**
     * @param array $criteria
     * @param bool  $removeEmpty
     *
     * @return array
     */
    public function sanatiseCriteria(array $criteria, $removeEmpty = true)
    {
        $sanitisedCriteria = array();

        foreach ($criteria as $key => $value) {

            if ($value instanceof ArrayCollection) {
                $value = $this->transformCollectionToIdArray($value);
            } elseif ($value instanceof AbstractEntity) {
                $value = $value->getId();
            }

            // If not an empty value or a zero value
            if (false == empty($value) || 0 === $value || "0" === $value) {
                $sanitisedCriteria[$key] = $value;
            }
        }

        return $sanitisedCriteria;
    }
    
    /**
     * Take a collection and remap the collection to the id
     * 
     * @param $collection
     * @return array
     * @throws \Exception
     */
    public function transformCollectionToIdArray($collection)
    {
        if(!$collection instanceof \Iterator) {
            throw new \Exception('Collection is not instance of Iterator');
        }

        $ids = array();

        foreach($collection as $value) {
            $ids[] = is_array($value) ?  $value['id'] : $value->getId();
        }

        return array_unique($ids);
    }
    
}
