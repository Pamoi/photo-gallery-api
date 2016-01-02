<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="photos")
 * @ORM\Entity()
 */
class Photo
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="Album", inversedBy="photos")
     */
    private $album;

    /**
     * @ORM\ManyToMany(targetEntity="Comment")
     * @ORM\JoinTable(name="photos_comments",
     *      joinColumns={@ORM\JoinColumn(name="photo_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="comment_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $comments;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="datetime")
     */
    private $uploadDate;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }
}