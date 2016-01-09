<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="photos")
 * @ORM\Entity()
 * @ORM\EntityListeners({"AppBundle\EventListener\PhotoListener"})
 */
class Photo
{
    /**
     * @var string $DATE_FORMAT
     *
     * The format used to represent date and time in this class.
     */
    public static $DATE_FORMAT = 'd-m-Y H:i:s';

    /**
     * @var string $MIN_PREFIX
     *
     * Prefix to append before the file name of the photo for the thumbnail version.
     */
    public static $MIN_PREFIX = 'thumb-';

    /**
     * @var string RESIZED_PREFIX
     *
     * Prefix to append before the file name of the photo for the resized version.
     */
    public static $RESIZED_PREFIX = 'resized-';

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @Assert\Valid()
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="Album", inversedBy="photos")
     *
     * @Assert\Valid()
     */
    private $album;

    /**
     * @ORM\ManyToMany(targetEntity="Comment", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="photos_comments",
     *      joinColumns={@ORM\JoinColumn(name="photo_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="comment_id", referencedColumnName="id", unique=true)}
     *      )
     *
     * @Assert\Valid()
     */
    private $comments;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Assert\DateTime()
     */
    private $date;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Assert\DateTime()
     */
    private $uploadDate;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $extension;

    /**
     * @Assert\Image(maxSize="12M")
     */
    private $file;

    /**
     * @var mixed temp variable used to store the photo's id and extension to delete
     * files after the entity has been removed from database.
     */
    private $tempFilename;

    /**
     * Validation constraint checking that the file is set if the photo is being created (id is null).
     *
     * @Assert\IsTrue(message="File must be set.")
     */
    public function isFileNotEmpty()
    {
        return null === $this->id ?
            (null !== $this->getFile())
            : true;
    }

    /**
     * Photo constructor.
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->date = new \DateTime();
        $this->uploadDate = new \DateTime();
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
     * Set the date at which the photo was taken
     *
     * @param \DateTime $date
     *
     * @return Photo
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
     * Set uploadDate
     *
     * @param \DateTime $uploadDate
     *
     * @return Photo
     */
    public function setUploadDate($uploadDate)
    {
        $this->uploadDate = $uploadDate;

        return $this;
    }

    /**
     * Get uploadDate
     *
     * @return \DateTime
     */
    public function getUploadDate()
    {
        return $this->uploadDate;
    }

    /**
     * Set author
     *
     * @param \AppBundle\Entity\User $author
     *
     * @return Photo
     */
    public function setAuthor(User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \AppBundle\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set album
     *
     * @param \AppBundle\Entity\Album $album
     *
     * @return Photo
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

    /**
     * Add comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return Photo
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
     * Set file
     *
     * @param UploadedFile $file
     *
     * @return Photo
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set extension
     *
     * @param string $extension
     *
     * @return Photo
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set tempFilename
     *
     * @param string $filename
     *
     * @return Photo
     */
    public function setTempFilename($filename)
    {
        $this->tempFilename = $filename;

        return $this;
    }

    /**
     * Get tempFilename
     *
     * @return string
     */
    public function getTempFilename()
    {
        return $this->tempFilename;
    }

    /**
     * Produces an array containing public data from this photo, ready
     * to be encoded as JSON.
     *
     * @return array
     */
    public function toJson()
    {
        $comments = array();

        foreach ($this->comments as $comment) {
            $comments[] = $comment->toJson();
        }

        $data = array(
            'id' => $this->getId(),
            'date' => $this->getDate()->format(static::$DATE_FORMAT),
            'uploadDate' => $this->getUploadDate()->format(static::$DATE_FORMAT),
            'author' => $this->getAuthor()->toJson(),
            'comments' => $comments,
            'url' => $this->getUrl(),
            'thumbUrl' => $this->getThumbUrl(),
            'resizedUrl' => $this->getResizedUrl()
        );

        return $data;
    }

    /**
     * Get the public url to access this photo.
     *
     * @return null|string
     */
    public function getUrl()
    {
        return (null === $this->id OR null === $this->extension)
            ? null
            : $this->getUploadDir() . $this->id . '.' . $this->extension;
    }

    /**
     * Get the url of a thumbnail of this photo.
     *
     * @return null|string
     */
    public function getThumbUrl()
    {
        return (null === $this->id OR null === $this->extension)
            ? null
            : $this->getUploadDir() . static::$MIN_PREFIX . $this->id . '.' . $this->extension;
    }

    /**
     * Get the url of a sized down version of this photo.
     *
     * @return null|string
     */
    public function getResizedUrl()
    {
        return (null === $this->id OR null === $this->extension)
            ? null
            : $this->getUploadDir() . static::$RESIZED_PREFIX . $this->id . '.' . $this->extension;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'photos/';
    }
}
