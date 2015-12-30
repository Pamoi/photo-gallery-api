<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddUserCommand extends Command
{
    private $em;
    private $encoder;
    private $logger;

    public function __construct(EntityManager $entityManager,
                                UserPasswordEncoderInterface $encoder, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->encoder = $encoder;
        $this->logger = $logger;

        parent::__construct();
    }

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
        $hashedPassword = $this->encoder->encodePassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $user->setUsername($name);
        $user->setEmail($email);

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $this->em->persist($user);
        $this->em->flush();

        $this->logger->info('Created user ' . $name . ' from command line');

        $output->writeln('<info>Added user ' . $name . ' to database</info>');
    }
}