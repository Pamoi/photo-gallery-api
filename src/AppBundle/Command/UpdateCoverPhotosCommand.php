<?php

namespace AppBundle\Command;

use AppBundle\Entity\Photo;
use AppBundle\Service\PhotoResizer\PhotoResizerInterface;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

class UpdateCoverPhotosCommand extends AbstractPhotoUpdateCommand
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
            ->setName('photos:update:cover')
            ->setDescription('Updates the cover version of all the photos')
        ;
    }

    protected function updatePhoto(Photo $photo)
    {
        $this->resizeService->cropToAspectRatio(
            $this->uploadRootDir . $photo->getFilename(),
            $this->uploadRootDir . $photo->getCoverFilename(),
            Photo::$COVER_ASPECT_RATIO,
            Photo::$COVER_WIDTH);
    }
}
