<?php

namespace AppBundle\Command;

use AppBundle\Entity\Photo;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateArchivesCommand extends Command
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
		->setName('archives:update')
		->setDescription('Updates the album archives')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$albums = $this->em->getRepository('AppBundle:Album')->findAll();
		$albumCount = count($albums);
		$i = 0;

		$output->writeln('<info>Updating archives...</info>');

		foreach ($albums as $album) {
			$filename = $this->uploadRootDir . $album->getArchiveName();
			
			if (file_exists($filename)) {
				unlink($filename);
			}
			
			$zip = new \ZipArchive();
			
			if ($zip->open($filename, \ZipArchive::CREATE) !== true) {
				$this->error('Unable to create archive for file ' . $filename);
			}
			
			foreach ($album->getPhotos() as $photo) {
				$photoFilename = $this->uploadRootDir . $photo->getFilename();
				
				if ($zip->addFile($photoFilename, $photo->getFilename()) !== true) {
					$this->error('Unable to add file ' . $photoFilename . ' to archive.');
				}
			}
			
			if ($zip->close() !== true) {
				$this->error('Unable to close archive ' . $filename);
			}
			
			$i++;
			$output->writeln($i . '/' . $albumCount);
		}

		$this->logger->info('Updated archives.');

		$output->writeln('<info>Done</info>');
	}
	
	protected function error($message)
	{
		$this->logger->info($message);
		$output->writeln($message);
	}
}
