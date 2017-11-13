<?php

namespace AppBundle\Command;

use AppBundle\Entity\Photo;
use AppBundle\Service\PhotoResizer\PhotoResizerInterface;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

class UpdateResizedPhotosCommand extends AbstractPhotoUpdateCommand
{
    private $uploadRootDir;
    private $resizeService;

    public function __construct(
        EntityManager $entityManager,
        LoggerInterface $logger,
        $uploadRootDir,
        PhotoResizerInterface $resizeService)
    {
        if (substr($uploadRootDir, -1) != '/') {
            $uploadRootDir = $uploadRootDir . '/';
        }

        $this->uploadRootDir = $uploadRootDir;
        $this->resizeService = $resizeService;

        parent::__construct($entityManager, $logger);
    }

    protected function configure()
    {
        $this
            ->setName('photos:update:resized')
            ->setDescription('Updates the sized down version of all the photos')
        ;
    }

    protected function updatePhoto(Photo $photo)
    {
        $this->resizeService->resize(
            $this->uploadRootDir . $photo->getFilename(),
            $this->uploadRootDir . $photo->getResizedFilename(),
            Photo::$MAX_RESIZED_WIDTH,
            Photo::$MAX_RESIZED_HEIGHT);
    }
}
