<?php

namespace mgate\SuiviBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PhaseRepository.
 */
class PhaseRepository extends EntityRepository
{
    /**
     * @param \mgate\SuiviBundle\Entity\Etude $etude
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getByEtudeQuery(Etude $etude)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('p')
            ->from('mgateSuiviBundle:Phase', 'p')
            ->where('p.etude = :etude')
            ->setParameter('etude', $etude);

        return $qb;
    }
}
