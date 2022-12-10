<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTokenCommand extends Command
{
    protected static $defaultName = 'app:create-token';

    public function __construct(private UserRepository $userRepo)
    {
        parent::__construct();
    }

    protected function configure()
    {
        parent::configure();
        $this->setDescription('Creates a new token')
            ->setHelp('This command allows you to create token upon receipt of a username and password')
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('generating...');

        $usr = $input->getArgument('username');
        $pwd = $input->getArgument('password');

        $user = $this->userRepo->findOneByUsernameAndPassword($usr, $pwd);
        if (null === $user) {
            $output->writeln('<error>User with such credentials is not exists</error>');
            return Command::FAILURE;
        }

        $tokenStr = $this->genToken();
        $output->writeln('<info>Your token is '.$tokenStr.'</info>');

        return Command::SUCCESS;
    }

    private function genToken(): string
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf(
            '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );
    }
}