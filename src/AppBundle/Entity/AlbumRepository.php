<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

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
}
