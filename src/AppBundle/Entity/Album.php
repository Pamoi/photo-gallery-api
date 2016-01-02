<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="albums")
 * @ORM\Entity()
 */
class Album
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     */
    private $authors;

    /**
     * @ORM\OneToMany(targetEntity="Photo", mappedBy="album")
     */
    private $photos;

    /**
     * @ORM\ManyToMany(targetEntity="Comment")
     * @ORM\JoinTable(name="albums_comments",
     *      joinColumns={@ORM\JoinColumn(name="album_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="comment_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $comments;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
        $this->photos = new ArrayCollection();
        $this->coments = new ArrayCollection();
    }
}