<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateUserCommand
 * @package App\Command
 */
class CreateUserCommand extends ContainerAwareCommand
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * CreateUserCommand constructor.
     * @param ManagerRegistry $doctrine
     * @param null $name
     */
    public function __construct(ManagerRegistry $doctrine, $name = null) {
        parent::__construct($name);
        $this->doctrine = $doctrine;
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this
            ->setName('user:create')
            ->setDescription('Command for create new moderator')
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the user.')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user.')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the user.')
            ->addArgument('role', InputArgument::OPTIONAL, 'Role')
        ;
    }

    /**
     * @inheritdoc
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        /** @var UserRepository $repository */
        $em = $this->doctrine->getManager();
        $repository = $em->getRepository('App:User');
        $foundedUser = $repository->loadUserByUsername($input->getArgument('username'), $input->getArgument('email'));
        if(!$foundedUser) {
            $user = new User();
            $roles = [User::ROLE_DEFAULT];
            if ($role = $input->getArgument('role')) {
                switch ($role) {
                    case 'admin':  $roles[] = User::ROLE_ADMIN; break;
                }
            }
            $user
                ->setUsername($input->getArgument('username'))
                ->setEmail($input->getArgument('email'))
                ->setPassword($input->getArgument('password'))
                ->setRoles($roles)
                ->setEnabled(true)
            ;

            $em->persist($user);
            $em->flush();
            $output->writeln('Moderator created');
        } else {
            $output->writeln('Moderator already is exists.');
        }
    }
}
