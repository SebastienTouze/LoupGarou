<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class VoteRepository extends EntityRepository
{
    public function findPersoneElue($type, $jour)
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        
        $queryBuilder->select('COUNT(v.votePour) AS nbVotes')
                     ->addSelect('u.id')
                     ->from('AppBundle:Vote', 'v')
                     ->join('v.votePour', 'u')
                     ->where('v.type = :type')
                        ->setParameter('type', $type)
                     ->andWhere('v.jour = :jour')
                        ->setParameter('jour', $jour)
                     ->orderBy('nbVotes', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }
    
    public function findNumberVoter($voteType, $day)
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        
        $queryBuilder->select('COUNT(v.votePour) AS nbVotes')
                     ->from('AppBundle:Vote', 'v')
                     ->where('v.type = :type')
                        ->setParameter('type', $voteType)
                     ->andWhere('v.jour = :jour')
                        ->setParameter('jour', $day)
                     ->orderBy('nbVotes', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }
}
