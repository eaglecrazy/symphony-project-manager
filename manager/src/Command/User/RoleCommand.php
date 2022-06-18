<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Model\User\Entity\User\Role;
use App\Model\User\UseCase\Role\Handler;
use App\ReadModel\User\UserFetcher;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use App\Model\User\UseCase\Role\Command as ChangeRoleCommand;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RoleCommand extends Command
{
    /**
     * @var UserFetcher
     */
    private $users;

    /**
     * @var Handler
     */
    private $handler;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(UserFetcher $users, ValidatorInterface $validator, Handler $handler)
    {
        parent::__construct();
        $this->users     = $users;
        $this->handler   = $handler;
        $this->validator = $validator;
    }

    protected function configure()
    {
        $this
            ->setName('user:role')
            ->setDescription('Changes user role');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $email = $helper->ask($input, $output, new Question('Email: '));

        $user = $this->users->findByEmail($email);

        if (!$user) {
            throw new LogicException('User not found');
        }

        $command = new ChangeRoleCommand($user->id);

        $roles = [Role::USER, Role::ADMIN];

        $command->role = $helper->ask($input, $output, new ChoiceQuestion('Role: ', $roles, 0));

        $violations = $this->validator->validate($command);

        if ($violations->count()) {
            foreach ($violations as $violation) {
                $output->writeln('<error>' . $violation->getPropertyPath() . ': ' . $violation->getMessage() . '</error>');
            }

            return;
        }

        $this->handler->handle($command);

        $output->writeln('<info>Done</info>');
    }
}
