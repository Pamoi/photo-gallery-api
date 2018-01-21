<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * UserRepository
 *
 * Repository for the Album entity.
 */
class AlbumRepository extends EntityRepository
{
    public function loadPage($pageNumber, $albumsPerPage)
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.creationDate', 'DESC')
            ->setFirstResult(($pageNumber - 1) * $albumsPerPage)
            ->setMaxResults($albumsPerPage)
            ->getQuery()
            ->getResult();
    }

    public function afterDate($date, $limit)
    {
        $qb = $this->createQueryBuilder('a');

        return $qb
            ->where('a.creationDate > :date')
            ->orderBy('a.creationDate', 'DESC')
            ->setMaxResults($limit)
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    public function beforeDate($date, $limit)
    {
        $qb = $this->createQueryBuilder('a');

        return $qb
            ->where('a.creationDate < :date')
            ->orderBy('a.creationDate', 'DESC')
            ->setMaxResults($limit)
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get an album at random. IMPORTANT: The underlying SQL query is very inefficient, consider replacing it
     * if running it on a large table !
     *
     * @return Album
     */
    public function getRandomAlbum()
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');

        $query = $this->getEntityManager()
            ->createNativeQuery('SELECT id FROM albums ORDER BY RAND() LIMIT 1', $rsm);
        $id = $query->getResult()[0]['id'];

        return $this->find($id);
    }
}
