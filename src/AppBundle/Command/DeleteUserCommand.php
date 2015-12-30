<?php

namespace AppBundle\Command;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteUserCommand extends Command
{
    private $em;
    private $logger;

    public function __construct(EntityManager $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('user:delete')
            ->setDescription('Deletes a user')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of the user to delete'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        $repository = $this->em->getRepository('AppBundle:User');
        $user = $repository->loadUserByUsername($name);

        if (null === $user) {
            throw new \InvalidArgumentException('No user with name ' . $name . ' was found in the database');
        }

        $this->em->remove($user);
        $this->em->flush();

        $this->logger->info('Deleted user ' . $name . ' from command line');

        $output->writeln('<info>Deleted user ' . $name . ' from database</info>');
    }
}