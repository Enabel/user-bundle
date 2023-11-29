<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Command\User;

use Doctrine\ORM\EntityManagerInterface;
use Enabel\UserBundle\Entity\User;
use Enabel\UserBundle\Repository\UserRepository;
use Enabel\UserBundle\Utils\Validator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

#[AsCommand(
    name: 'enabel:user:demote',
    description: 'Demote a admin as user',
)]
class DemoteCommand extends Command
{
    private UserRepository $userRepository;
    private Validator $validator;
    private SymfonyStyle $io;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        Validator $validator
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp($this->getCommandHelp())
            ->addArgument('email', InputArgument::OPTIONAL, 'The email of the user')
            ->addOption(
                'super-admin',
                null,
                InputOption::VALUE_NONE,
                'If set, the super administrator is demoted as an user'
            );
    }

    /**
     * Optional method, first one executed for a command after configure()
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * This method is executed after initialize() and before execute(). Its purpose
     * is to check if some of the options/arguments are missing and interactively
     * ask the user for those values.
     *
     * This method is a nice way to fall back and prevent errors.
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (
            null !== $input->getArgument('email')
        ) {
            return;
        }

        $this->io->title('User demote interactive wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console ' . self::$defaultName . ' email@example.com',
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
        ]);

        // Ask for the email if it's not defined
        $question = new Question('Email:');
        $question->setAutocompleterValues($this->userRepository->suggestionsAutocompleteEmail());
        $question->setValidator([$this->validator, 'validateEmail']);

        $email = $this->io->askQuestion($question);

        $input->setArgument('email', $email);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('user-demote-command');

        // Retrieve arguments data
        /** @var string $email */
        $email = $input->getArgument('email');
        /** @var bool $isSuperAdmin */
        $isSuperAdmin = $input->getOption('super-admin');

        // make sure to validate the user data is correct
        $user = $this->validateUserData($email);

        // demote the user
        if ($isSuperAdmin) {
            $user->removeRole('ROLE_SUPER_ADMIN');
        } else {
            $user->removeRole('ROLE_ADMIN');
        }

        // Save user in DB
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->io->success(
            sprintf(
                '%s (%s) was successfully demoted as user',
                $user->getDisplayName(),
                $user->getUserIdentifier()
            )
        );

        $event = $stopwatch->stop('user-demote-command');
        if ($output->isVerbose()) {
            $this->io->comment(
                sprintf(
                    'Demote user id: %d / Elapsed time: %.2f ms / Consumed memory: %.2f MB',
                    $user->getId(),
                    $event->getDuration(),
                    $event->getMemory() / (1024 ** 2)
                )
            );
        }

        return Command::SUCCESS;
    }

    private function validateUserData(?string $email): User
    {
        // validate data if is not this input means interactive.
        $this->validator->validateEmail($email);

        // check if a user with this email exists.
        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (null === $user) {
            throw new RuntimeException(sprintf('There is no user registered with the "%s" email.', $email));
        }

        return $user;
    }

    /**
     * The command help is usually included in the configure() method, but it's too long.
     * define a separate method to maintain the code readability.
     */
    private function getCommandHelp(): string
    {
        return <<<'HELP'
The <info>%command.name%</info> command demote a admin as a user:

  <info>php %command.full_name%</info> <comment>email</comment>
  
By default the command demote a administrator user (ROLE_ADMIN) as user (ROLE_USER)
To demote a super administrator (ROLE_SUPER_ADMIN) as user (ROLE_USER), 
add the <comment>--super-admin</comment> option:

  <info>php %command.full_name%</info> email <comment>--super-admin</comment>
  
If you omit any of the required arguments, the command will ask you to
provide the missing values:

  # command will ask you for all arguments
  <info>php %command.full_name%</info>

HELP;
    }
}
