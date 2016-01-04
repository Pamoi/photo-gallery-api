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

    public function loadUserByUsername($username)
    {
        $user = $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $user) {
            throw new UsernameNotFoundException('Unable to find an AppBundle:User object identified by ' . $username);
        }

        return $user;
    }
}
