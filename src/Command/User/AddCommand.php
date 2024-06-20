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
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Stopwatch\Stopwatch;

use function Symfony\Component\String\u;

#[AsCommand(
    name: 'enabel:user:add',
    description: 'Creates users and stores them in the database',
)]
class AddCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private SymfonyStyle $io;
    private UserPasswordHasherInterface $passwordHasher;
    private UserRepository $userRepository;
    private Validator $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        Validator $validator
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp($this->getCommandHelp())
            // commands can optionally define arguments and/or options (mandatory and optional)
            // see https://symfony.com/doc/current/components/console/console_arguments.html
            ->addArgument('email', InputArgument::OPTIONAL, 'The email of the new user')
            ->addArgument('password', InputArgument::OPTIONAL, 'The plain password of the new user')
            ->addArgument('display-name', InputArgument::OPTIONAL, 'The display name of the new user')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'If set, the user is created as an administrator')
            ->addOption(
                'super-admin',
                null,
                InputOption::VALUE_NONE,
                'If set, the user is created as an super administrator'
            )
        ;
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
            && null !== $input->getArgument('password')
            && null !== $input->getArgument('display-name')
        ) {
            return;
        }

        $this->io->title('User creation interactive wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console ' . self::getDefaultName() . ' email@example.com password display-name',
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
        ]);

        // Ask for the email if it's not defined
        $email = $input->getArgument('email');
        if (null !== $email) {
            // @codeCoverageIgnoreStart
            $this->io->text(' > <info>Email</info>: ' . $email);
            // @codeCoverageIgnoreEnd
        } else {
            $email = $this->io->ask('Email', null, [$this->validator, 'validateEmail']);
            $input->setArgument('email', $email);
        }

        // Ask for the password if it's not defined
        /** @var string|null $password */
        $password = $input->getArgument('password');
        if (null !== $password) {
            // @codeCoverageIgnoreStart
            $this->io->text(' > <info>Password</info>: ' . u('*')->repeat(u($password)->length()));
            // @codeCoverageIgnoreEnd
        } else {
            $password = $this->io->askHidden(
                'Password (your type will be hidden)',
                [$this->validator, 'validatePassword']
            );
            $input->setArgument('password', $password);
        }

        // Ask for the display name if it's not defined
        $displayName = $input->getArgument('display-name');
        if (null !== $displayName) {
            // @codeCoverageIgnoreStart
            $this->io->text(' > <info>Display name</info>: ' . $displayName);
            // @codeCoverageIgnoreEnd
        } else {
            $displayName = $this->io->ask('Display name', null, [$this->validator, 'validateDisplayName']);
            $input->setArgument('display-name', $displayName);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('user-add-command');

        // Retrieve arguments data
        /** @var string $email */
        $email = $input->getArgument('email');
        /** @var string $plainPassword */
        $plainPassword = $input->getArgument('password');
        /** @var string $displayName */
        $displayName = $input->getArgument('display-name');
        /** @var bool $isAdmin */
        $isAdmin = $input->getOption('admin');
        /** @var bool $isSuperAdmin */
        $isSuperAdmin = $input->getOption('super-admin');

        // make sure to validate the user data is correct
        $this->validateUserData($email, $plainPassword, $displayName);
        $userClass = $this->userRepository->getClassName();

        // create the user
        /** @var User $user */
        $user = new $userClass();
        $user->setEmail($email);
        $user->setDisplayName($displayName);
        $user->setRoles(['ROLE_USER']);
        $userType = 'User';
        if ($isAdmin) {
            $user->addRole('ROLE_ADMIN');
            $userType = 'Administrator user';
        }
        if ($isSuperAdmin) {
            $user->addRole('ROLE_SUPER_ADMIN');
            $userType = 'Super administrator user';
        }

        // Hash its password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // Save user in DB
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->io->success(
            sprintf(
                '%s was successfully created: %s (%s)',
                $userType,
                $user->getDisplayName(),
                $user->getUserIdentifier()
            )
        );

        $event = $stopwatch->stop('user-add-command');
        if ($output->isVerbose()) {
            $this->io->comment(
                sprintf(
                    'New user id: %d / Elapsed time: %.2f ms / Consumed memory: %.2f MB',
                    $user->getId(),
                    $event->getDuration(),
                    $event->getMemory() / (1024 ** 2)
                )
            );
        }

        return Command::SUCCESS;
    }

    private function validateUserData(?string $email, ?string $password, ?string $displayName): void
    {
        // validate data if is not this input means interactive.
        $this->validator->validatePassword($password);
        $this->validator->validateEmail($email);
        $this->validator->validateDisplayName($displayName);

        // check if a user with the same email already exists.
        $existingEmail = $this->userRepository->findOneBy(['email' => $email]);

        if (null !== $existingEmail) {
            throw new RuntimeException(sprintf('There is already a user registered with the "%s" email.', $email));
        }
    }

    /**
     * The command help is usually included in the configure() method, but it's too long.
     * define a separate method to maintain the code readability.
     */
    private function getCommandHelp(): string
    {
        return <<<'HELP'
The <info>%command.name%</info> command creates new users and saves them in the database:

  <info>php %command.full_name%</info> <comment>email password display-name</comment>
  
By default the command creates regular users. 
To create administrator users (ROLE_ADMIN), 
add the <comment>--admin</comment> option:

  <info>php %command.full_name%</info> email password display-name <comment>--admin</comment>

To create super administrator users (ROLE_SUPER_ADMIN), 
add the <comment>--super-admin</comment> option:

  <info>php %command.full_name%</info> email password display-name <comment>--super-admin</comment>
  
If you omit any of the required arguments, the command will ask you to
provide the missing values:

  # command will ask you for the password and display-name
  <info>php %command.full_name%</info> <comment>email</comment>
  
  # command will ask you for all arguments
  <info>php %command.full_name%</info>

HELP;
    }
}
