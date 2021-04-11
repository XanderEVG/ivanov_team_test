<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CreateUserCommand.
 * Создает пользователя
 */
class CreateUserCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'users:create';

    /**
     * @var EntityManagerInterface
     */
    private $doctrine;

    /**
     * CreateUserCommand constructor.
     *
     * @param EntityManagerInterface $em
     * @param string|null            $name
     */
    public function __construct(EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);
        $this->doctrine = $em;
    }

    public function configure(): void
    {
        $this
            ->setDescription('create users')
            ->addArgument('login', InputArgument::REQUIRED, 'login')
            ->addArgument('password', InputArgument::REQUIRED, 'password')
            ->addArgument('email', InputArgument::REQUIRED, 'email')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $login = $input->getArgument('login');
        $password = $input->getArgument('password');
        $email = $input->getArgument('email');

        $login = filter_var($login, FILTER_SANITIZE_STRING);
        $password = filter_var($password, FILTER_SANITIZE_STRING);
        $email = filter_var($email, FILTER_SANITIZE_STRING);

        //TODO валидация



        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Создание пользователя %s', $login));

        $password = password_hash($password, PASSWORD_DEFAULT);
        if ($password === null or $password ===false) {
            $io->error('Ошибка, не удалось захешировать пароль');
            return 1;
        }

        $user = new User();
        $user->setLogin($login);
        $user->setPassword($password);
        $user->setEmail($email);
        try {
            $this->doctrine->persist($user);
            $this->doctrine->flush();
        } catch (Exception $e) {
            $io->error('Ошибка, не удалось сохранить пользователя: ' . $e->getMessage());
            return 1;
        }
        $io->success("Пользователь создан");

        return 0;
    }

}
