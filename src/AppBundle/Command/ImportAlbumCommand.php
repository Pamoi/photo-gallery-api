<?php

namespace AppBundle\Command;

use AppBundle\Entity\Album;
use AppBundle\Entity\AlbumComment;
use AppBundle\Entity\Photo;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportAlbumCommand extends Command
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
            ->setName('import:albums')
            ->setDescription('Import albums from JSON and photo files')
            ->addArgument('path', InputArgument::REQUIRED, 'path of the folder containing the JSON file and photo files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $users = $this->em->getRepository('AppBundle:User')->findAll();
        $user_map = [];

        foreach ($users as $user) {
            $user_map[$user->getId()] = $user;
        }

        $albums = json_decode(file_get_contents($input->getArgument('path') . '/albums.json'), true);

        $albumCount = count($albums);
        $progressBar = new ProgressBar($output, $albumCount);

        foreach ($albums as $album) {
            $a = new Album();

            $a->setTitle($album['title']);
            $a->setDescription($album['description']);
            $date = new \DateTime($album['date']);
            $a->setDate($date);
            $a->setCreationDate($date);

            foreach ($album['authors'] as $id) {
                $a->addAuthor($user_map[$id]);
            }

            foreach ($album['photos'] as $photo) {
                try {
                    $p = new Photo();

                    $p->setAuthor($user_map[$photo['authorId']]);
                    $date = new \DateTime($photo['uploadDate']);
                    $p->setDate($date);
                    $p->setUploadDate($date);
                    $file = new UploadedFile($input->getArgument('path') . '/' . $photo['filename'],
                        $photo['filename'],
                        'image/jpeg',
                        null,
                        null,
                        true);
                    $p->setFile($file);

                    $a->addPhoto($p);
                } catch (\Exception $e) {
                    $output->writeln('An error occurred while creating photo: ' . $e->getMessage());
                }
            }

            foreach ($album['comments'] as $comment) {
                $c = new AlbumComment();

                $date = new \DateTime($comment['date']);
                $c->setDate($date);
                $c->setAuthor($user_map[$comment['authorId']]);
                $c->setText($comment['text']);

                $a->addComment($c);
            }

            $this->em->persist($a);
            $this->em->flush($a);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->logger->info('Imported albums.');

        $output->writeln('');
        $output->writeln('<info>Done</info>');
    }

}