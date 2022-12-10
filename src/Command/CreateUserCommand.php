<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';

    public function __construct(private UserRepository $repository)
    {
        parent::__construct();
    }

    protected function configure()
    {
        parent::configure();
        $this->setDescription('Creates a new user')
            ->setHelp('This command allows you to create new user with username and password')
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $usr = $input->getArgument('username');
        $pwd = $input->getArgument('password');

        $user = $this->repository->findOneByUsername($usr);
        if (null !== $user) {
            $output->writeln('<error>User with such username is already exists</error>');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setUsername($usr)
            ->setPassword($pwd);
        $this->repository->save($user, true);

        $output->writeln("<info>Successful creating. Username: $usr Password: $pwd</info>");

        return Command::SUCCESS;
    }
}