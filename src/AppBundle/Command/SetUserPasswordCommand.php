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

class SetUserPasswordCommand extends Command
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
            ->setName('user:password')
            ->setDescription('Sets a new password for a user')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of the user'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'the new password'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $pwd = $input->getArgument('password');

        $passwordLength = strlen($pwd);
        if ($passwordLength < 4 || $passwordLength > 4096) {
            throw new \InvalidArgumentException('Password length must be between 4 and 4096 characters');
        }

        $repository = $this->em->getRepository('AppBundle:User');
        $user = $repository->loadUserByUsername($name);

        $hashedPassword = $this->encoder->encodePassword($user, $pwd);
        $user->setPassword($hashedPassword);

        $this->em->persist($user);
        $this->em->flush();

        $this->logger->info('Updated ' . $name . '\'s password from command line');

        $output->writeln('<info>Modified ' . $name . '\'s password</info>');
    }
}
