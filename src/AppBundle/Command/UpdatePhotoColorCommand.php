<?php
/**
 * Created by PhpStorm.
 * User: matthieu
 * Date: 19/11/17
 * Time: 22:23
 */

namespace AppBundle\Command;


use AppBundle\Entity\Photo;
use AppBundle\Service\ColorExtractor\ColorExtractorInterface;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

class UpdatePhotoColorCommand extends AbstractPhotoUpdateCommand
{
    private $uploadRootDir;
    private $colorExtractor;

    public function __construct(
        EntityManager $entityManager,
        LoggerInterface $logger,
        $uploadRootDir,
        ColorExtractorInterface $colorExtractor)
    {
        if (substr($uploadRootDir, -1) != '/') {
            $uploadRootDir = $uploadRootDir . '/';
        }

        $this->uploadRootDir = $uploadRootDir;
        $this->colorExtractor = $colorExtractor;

        parent::__construct($entityManager, $logger);
    }

    protected function configure()
    {
        $this
            ->setName('photos:update:color')
            ->setDescription('Updates the dominant color of all the photos')
        ;
    }

    protected function updatePhoto(Photo $photo)
    {
        $filename = $this->uploadRootDir . $photo->getCoverFilename();
        $color = $this->colorExtractor->extractMainColor($filename);
        $photo->setDominantColor($color);
    }
}