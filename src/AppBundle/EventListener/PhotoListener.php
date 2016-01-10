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

        $photo->getFile()->move($this->uploadRootDir, $photo->getFilename());

        $resizer = new ImagickPhotoResizer($this->uploadRootDir . $photo->getFilename());

        $resizer->resize($this->uploadRootDir . $photo->getResizedFilename(), 1000, 700);
        $resizer->resizeToSquare($this->uploadRootDir . $photo->getThumbFilename(), 300);

        $photo->setFile(null);
    }

    /**
     * @ORM\PreRemove()
     */
    public function storeFilenameForRemove(Photo $photo, LifecycleEventArgs $event)
    {
        $photo->storeTempFileNames();
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeFiles(Photo $photo, LifecycleEventArgs $event)
    {
        $fileNames = $photo->getTempFileNames();
        if (is_array($fileNames)) {
            foreach ($fileNames as $name) {
                $this->safeUnlink($this->uploadRootDir . $name);
            }
        }
    }

    private function safeUnlink($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}