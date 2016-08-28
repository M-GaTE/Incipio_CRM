<?php

namespace n7consulting\RhBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CompetenceRepository extends EntityRepository
{
    /**
     * Méthode retournant toutes les compétences et leurs membres associés.
     *
     * @return array
     */
    public function getCompetencesTree()
    {
        $qb = $this->_em->createQueryBuilder();

        $query = $qb->select('c')
            ->from('n7consultingRhBundle:Competence', 'c')
            ->leftJoin('c.membres', 'membres')
            ->addSelect('membres')
            ->orderBy('c.id', 'asc')
            ->getQuery();

        return $query->getResult();
    }
}
