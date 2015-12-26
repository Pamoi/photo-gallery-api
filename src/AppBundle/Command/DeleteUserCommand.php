<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteUserCommand extends ContainerAwareCommand
{
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

        $repository = $this->getContainer()->get('doctrine')->getRepository('AppBundle:User');
        $user = $repository->loadUserByUsername($name);

        if (null === $user) {
            throw new \InvalidArgumentException('No user with name ' . $name . ' was found in the database');
        }

        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->remove($user);
        $em->flush();

        $logger = $this->getContainer()->get('logger');
        $logger->info('Deleted user ' . $name . ' from command line');

        $output->writeln('<info>Deleted user ' . $name . ' from database</info>');
    }
}