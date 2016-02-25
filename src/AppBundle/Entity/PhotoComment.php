<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="photo_comments")
 * @ORM\Entity()
 */
class PhotoComment extends Comment
{
    /**
     * @ORM\ManyToOne(targetEntity="Photo", inversedBy="comments")
     *
     * @Assert\Valid()
     */
    private $photo;

    /**
     * Set photo
     *
     * @param \AppBundle\Entity\Photo $photo
     *
     * @return PhotoComment
     */
    public function setPhoto(Photo $photo = null)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return \AppBundle\Entity\Photo
     */
    public function getPhoto()
    {
        return $this->photo;
    }
}
