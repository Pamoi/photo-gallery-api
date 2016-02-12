<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="comments")
 * @ORM\Entity()
 */
class Comment implements PublicJsonInterface
{
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
     * @ORM\Column(type="text")
     *
     * @Assert\Length(min=2, max=10000)
     */
    private $text;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotNull()
     * @Assert\DateTime()
     */
    private $date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime()
     */
    private $editDate;

    /**
     * Comment constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->editDate = null;
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
     * Set text
     *
     * @param string $text
     *
     * @return Comment
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Comment
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
     * Set author
     *
     * @param \AppBundle\Entity\User $author
     *
     * @return Comment
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
     * Set editDate
     *
     * @param \DateTime $editDate
     *
     * @return Comment
     */
    public function setEditDate($editDate)
    {
        $this->editDate = $editDate;

        return $this;
    }

    /**
     * Get editDate
     *
     * @return \DateTime
     */
    public function getEditDate()
    {
        return $this->editDate;
    }

    /**
     * {@inheritdoc }
     */
    public function toJson()
    {
        $editDate = $this->getEditDate() === null ? null : $this->getEditDate()->format(\DateTime::ISO8601);

        $data = array(
            'id' => $this->getId(),
            'date' => $this->getDate()->format(\DateTime::ISO8601),
            'editDate' => $editDate,
            'author' => $this->getAuthor()->toJson(),
            'text' => $this->getText()
        );

        return $data;
    }
}
