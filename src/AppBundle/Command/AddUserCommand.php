<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('user:add')
            ->setDescription('Creates a new user')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of the user'
            )
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'email address of the user'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'password of the user'
            )
            ->addArgument(
                'roles',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'roles of the user'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $email = $input->getArgument('email');
        $plainPassword = $input->getArgument('password');
        $roles = $input->getArgument('roles');

        $passwordLength = strlen($plainPassword);
        if ($passwordLength < 4 || $passwordLength > 4096) {
            throw new \InvalidArgumentException('Password length must be between 4 and 4096 characters');
        }

        $user = new User();
        $encoder = $this->getContainer()->get('security.password_encoder');
        $hashedPassword = $encoder->encodePassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $user->setUsername($name);
        $user->setEmail($email);

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        $logger = $this->getContainer()->get('logger');
        $logger->info('Creating user ' . $name . ' from command line');

        $output->writeln($name);
    }
}