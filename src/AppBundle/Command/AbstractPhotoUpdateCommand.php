<?php

namespace AppBundle\Command;

use AppBundle\Entity\Photo;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractPhotoUpdateCommand extends Command
{
    private $em;
    private $logger;

    public function __construct(EntityManager $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info("Updating photos with command " . $this->getName());

        $photos = $this->em->getRepository('AppBundle:Photo')->findAll();
        $photoCount = count($photos);

        $progressBar = new ProgressBar($output, $photoCount);
        $output->writeln('<info>Updating photos...</info>');

        foreach ($photos as $photo) {
            try {
                $this->updatePhoto($photo);
            } catch (\Exception $e) {
                $this->logger->error(
                    "Error while updating photos with command " . $this->getName() . ': ' . $e->getMessage());
                $output->writeln("Error while updating photo: " . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln("");
        $output->writeln('<info>Done</info>');

        $this->logger->info("Finished updating photos with command " . $this->getName());
    }

    abstract protected function updatePhoto(Photo $photo);
}
