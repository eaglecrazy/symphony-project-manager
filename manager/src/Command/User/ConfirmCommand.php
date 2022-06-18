<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Model\User\UseCase\SignUp\Confirm\Manual\Handler;
use App\ReadModel\User\UserFetcher;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use App\Model\User\UseCase\SignUp\Confirm\Manual\Command as ManualConfirmCommand;

class ConfirmCommand extends Command
{
    /**
     * @var UserFetcher
     */
    private $users;

    /**
     * @var Handler
     */
    private $handler;

    public function __construct(UserFetcher $users, Handler $handler)
    {
        parent::__construct();
        $this->users   = $users;
        $this->handler = $handler;
    }

    protected function configure()
    {
        $this
            ->setName('user:confirm')
            ->setDescription('Confirm signed up user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $email = $helper->ask($input, $output, new Question('Email: '));

        $user = $this->users->findByEmail($email);

        if (!$user) {
            throw new LogicException('User not found');
        }

        $command = new ManualConfirmCommand($user->id);

        $this->handler->handle($command);

        $output->writeln('<info>Done</info>');
    }
}
