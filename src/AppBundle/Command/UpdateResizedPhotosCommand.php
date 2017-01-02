<?php

namespace AppBundle\Command;

use AppBundle\Entity\Photo;
use AppBundle\Util\ImagickPhotoResizer;
use AppBundle\Util\PhotoResizingException;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateResizedPhotosCommand extends Command
{
    private $em;
    private $logger;
    private $uploadRootDir;

    public function __construct(EntityManager $entityManager, LoggerInterface $logger, $uploadRootDir)
    {
        if (substr($uploadRootDir, -1) != '/') {
            $uploadRootDir = $uploadRootDir . '/';
        }

        $this->em = $entityManager;
        $this->logger = $logger;
        $this->uploadRootDir = $uploadRootDir;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('photos:update')
            ->setDescription('Updates the resized version of all the photos')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $photos = $this->em->getRepository('AppBundle:Photo')->findAll();

        $output->writeln('<info>Updating resized photos...</info>');

        foreach ($photos as $photo) {
            try {
              $filename = $this->uploadRootDir . $photo->getFilename();
              $resizer = new ImagickPhotoResizer($filename);
              $resizer->resize($this->uploadRootDir . $photo->getResizedFilename(), 1920, 1080);
            } catch (PhotoResizingException $e) {
                $this->logger->info('Failed to resize photo ' . $filename);
                $output->writeln('<info>Failed to resize photo ' . $filename . '</info>');
            }
        }

        $this->logger->info('Updated resized photos.');

        $output->writeln('<info>Done</info>');
    }
}
