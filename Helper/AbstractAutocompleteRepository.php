<?php

namespace App\AppBundle\Helper;

use Doctrine\ORM\EntityRepository;
use App\AppBundle\Helper\Filters\IdFilter;
use App\AppBundle\Helper\AbstractRepository;

abstract class AbstractAutocompleteRepository extends AbstractRepository {

    /**
     * @param type $text
     * @param int $levenshteinPrecission
     * @return type
     */
    public function searchByText($text, $levenshteinPrecission = 80) {
        $queryBuilder = $this->createQueryBuilder('r');
        $queryBuilder->select('r');

        $or = $queryBuilder->expr()->orX();
        $or->add($queryBuilder->expr()->like('r.name', $queryBuilder->expr()->literal('%' . $text . '%')));
        $or->add($queryBuilder->expr()->gte('LEVENSHTEIN_RATIO(r.name, ' . $queryBuilder->expr()->literal($text) . ')', $levenshteinPrecission));

        $queryBuilder->andWhere($or);

        $queryBuilder->addOrderBy('r.searched', 'desc');
        $queryBuilder->addOrderBy('r.searchGroup', 'asc');
        $queryBuilder->addOrderBy('LEVENSHTEIN_RATIO(r.name, ' . $queryBuilder->expr()->literal($text) . ')', 'desc');
        $queryBuilder->addOrderBy('r.results', 'asc');

        $queryBuilder->andWhere($queryBuilder->expr()->gt('r.results', 0));
        $queryBuilder->andWhere('r.active = 1');

        return $queryBuilder->getQuery()->getResult();
    }

}
