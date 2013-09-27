<?php

namespace LG\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

class VoteRepository extends EntityRepository
{
    public function findPersoneElue($type, $jour)
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        
        $queryBuilder->select('COUNT(v.votePour) AS nbVotes')
                     ->addSelect('u.id')
                     ->from('LGUserBundle:Vote', 'v')
                     ->join('v.votePour', 'u')
                     ->where('v.type = :type')
                        ->setParameter('type', $type)
                     ->andWhere('v.jour = :jour')
                        ->setParameter('jour', $jour)
                     ->orderBy('nbVotes', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }
}
