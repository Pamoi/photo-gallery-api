<?php

namespace AppBundle\EventListener;

use AppBundle\Util\ImagickPhotoResizer;
use AppBundle\Entity\Photo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;

class PhotoListener
{
    private $uploadRootDir;

    /**
     * PhotoListener constructor.
     *
     * @param string $photoUploadDir The directory inside which the photo files will be saved.
     */
    public function __construct($photoUploadDir)
    {
        if (substr($photoUploadDir, -1) != '/') {
            $photoUploadDir = $photoUploadDir . '/';
        }

        $this->uploadRootDir = $photoUploadDir;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function saveExtension(Photo $photo, LifecycleEventArgs $event)
    {
        if (null !== $photo->getFile()) {
            $photo->setExtension($photo->getFile()->guessExtension());
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function saveFile(Photo $photo, LifecycleEventArgs $event)
    {
        if (null === $photo->getFile()) {
            return;
        }

        $filename = $photo->getId() . '.' . $photo->getExtension();
        $photo->getFile()->move($this->uploadRootDir, $filename);

        $resizer = new ImagickPhotoResizer($this->uploadRootDir . $filename);

        $resizer->resize($this->uploadRootDir . Photo::$RESIZED_PREFIX . $filename, 1000, 700);
        $resizer->resizeToSquare($this->uploadRootDir . Photo::$MIN_PREFIX . $filename, 300);

        $photo->setFile(null);
    }

    /**
     * @ORM\PreRemove()
     */
    public function storeFilenameForRemove(Photo $photo, LifecycleEventArgs $event)
    {
        $photo->setTempFilename($photo->getId() . '.' . $photo->getExtension());
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeFiles(Photo $photo, LifecycleEventArgs $event)
    {
        $filename = $photo->getTempFilename();
        if (null !== $filename) {
            $this->safeUnlink($this->uploadRootDir . $filename);
            $this->safeUnlink($this->uploadRootDir . Photo::$MIN_PREFIX . $filename);
            $this->safeUnlink($this->uploadRootDir . Photo::$RESIZED_PREFIX . $filename);
        }
    }

    private function safeUnlink($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}