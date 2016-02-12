<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="albums")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\AlbumRepository")
 */
class Album implements PublicJsonInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     *
     * @Assert\Valid()
     */
    private $authors;

    /**
     * @ORM\Column(type="string", length=60)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=60)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\Length(min=0, max=10000)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="Photo", mappedBy="album", cascade={"persist", "remove"})
     *
     * @Assert\Valid()
     */
    private $photos;

    /**
     * @ORM\ManyToMany(targetEntity="Comment", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="albums_comments",
     *      joinColumns={@ORM\JoinColumn(name="album_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="comment_id", referencedColumnName="id", unique=true)}
     *      )
     *
     * @Assert\Valid()
     */
    private $comments;

    /**
     * @ORM\Column(type="date")
     *
     * @Assert\DateTime()
     * @Assert\NotNull()
     */
    private $date;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Assert\DateTime()
     * @Assert\NotNull()
     */
    private $creationDate;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Assert\Type(type="bool")
     */
    private $isPublic;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
        $this->photos = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->date = new \DateTime();
        $this->creationDate = new \DateTime();
        $this->isPublic = true;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Album
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     *
     * @return Album
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Add author
     *
     * @param \AppBundle\Entity\User $author
     *
     * @return Album
     */
    public function addAuthor(User $author)
    {
        $this->authors[] = $author;

        return $this;
    }

    /**
     * Remove author
     *
     * @param \AppBundle\Entity\User $author
     */
    public function removeAuthor(User $author)
    {
        $this->authors->removeElement($author);
    }

    /**
     * Get authors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * Add photo
     *
     * @param \AppBundle\Entity\Photo $photo
     *
     * @return Album
     */
    public function addPhoto(Photo $photo)
    {
        $this->photos[] = $photo;
        $photo->setAlbum($this);

        return $this;
    }

    /**
     * Remove photo
     *
     * @param \AppBundle\Entity\Photo $photo
     */
    public function removePhoto(Photo $photo)
    {
        $this->photos->removeElement($photo);
    }

    /**
     * Get photos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhotos()
    {
        return $this->photos;
    }

    /**
     * Add comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return Album
     */
    public function addComment(Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \AppBundle\Entity\Comment $comment
     */
    public function removeComment(Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Album
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Album
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set isPublic
     *
     * @param boolean $isPublic
     *
     * @return Album
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * Get isPublic
     *
     * @return boolean
     */
    public function isPublic()
    {
        return $this->isPublic;
    }

    /**
     * {@inheritdoc }
     */
    public function toJson()
    {
        $photos = array();

        foreach ($this->photos as $photo) {
            $photos[] = $photo->toJson();
        }

        $comments = array();

        foreach ($this->comments as $comment) {
            $comments[] = $comment->toJson();
        }

        $authors = array();

        foreach ($this->authors as $author) {
            $authors[] = $author->toJson();
        }

        $data = array(
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'date' => $this->getDate()->format(\DateTime::ISO8601),
            'creationDate' => $this->getCreationDate()->format(\DateTime::ISO8601),
            'authors' => $authors,
            'photos' => $photos,
            'comments' => $comments
        );

        return $data;
    }
}
