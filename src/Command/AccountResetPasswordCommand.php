<?php

namespace App\Command;

use App\Entity\Account;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'account:reset-password',
    description: 'Reset a user account password',
)]
class AccountResetPasswordCommand extends Command
{
    public function __construct(
        private AccountRepository $accountRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::OPTIONAL, 'Username of the account to reset password')
            ->addOption('temp', 't', InputOption::VALUE_NONE, 'Temporary password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username');

        if (!$username) {
            $question = new Question('Enter the account email');
            $question->setAutocompleterCallback(
                fn(string $userInput): array => $this->accountRepository->autocompleteUsernames($userInput)
            );
            $username = $io->askQuestion($question);
        }

        $account = $this->accountRepository->findOneBy(['email' => $username]);
        if (!$account) {
            $io->error('No account found with this email.');

            return Command::FAILURE;
        }

        $password = $io->askHidden('Password'); // Do not display input on screen

        $sw = new Stopwatch();
        $sw->start('validation'); // Start stopwatch

        $violations = $this->validator->validate($password, [
            new PasswordStrength(),
            new NotCompromisedPassword()
        ]);
        $event = $sw->stop('validation'); // Stop stopwatch
        if ($output->isVerbose()) {
            $io->info('Validation time: ' . $event->getDuration() . ' ms.'); // Display time in ms
        }

        if (0 < $violations->count()) {
            foreach ($violations as $violation)
                $io->error($violation->getMessage());

            return Command::FAILURE;
        }

        $account->setPassword($this->passwordHasher->hashPassword($account, $password));
        if ($input->getOption('temp')) {
            // mark user to change password
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            "The password for account %s has been successfully reset.",
            $account->getEmail()
        ));

        return Command::SUCCESS;
    }
}
