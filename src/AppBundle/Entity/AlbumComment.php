<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="album_comments")
 * @ORM\Entity()
 */
class AlbumComment extends Comment
{
    /**
     * @ORM\ManyToOne(targetEntity="Album", inversedBy="comments")
     *
     * @Assert\Valid()
     */
    private $album;

    /**
     * Set album
     *
     * @param \AppBundle\Entity\Album $album
     *
     * @return AlbumComment
     */
    public function setAlbum(Album $album = null)
    {
        $this->album = $album;

        return $this;
    }

    /**
     * Get album
     *
     * @return \AppBundle\Entity\Album
     */
    public function getAlbum()
    {
        return $this->album;
    }
}
