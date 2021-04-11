<?php

declare(strict_types=1);

namespace App\Controller;

use App\Common\Exceptions\TwigException;
use App\Entity\Movie;
use App\Entity\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Cake\Validation\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Interfaces\RouteCollectorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class HomeController.
 */
class UsersController
{
    /**
     * @var RouteCollectorInterface
     */
    private $routeCollector;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * HomeController constructor.
     *
     * @param RouteCollectorInterface $routeCollector
     * @param Environment             $twig
     * @param EntityManagerInterface  $em
     */
    public function __construct(RouteCollectorInterface $routeCollector, Environment $twig, EntityManagerInterface $em)
    {
        $this->routeCollector = $routeCollector;
        $this->twig = $twig;
        $this->em = $em;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     * @throws TwigException
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();
        $msg = $params['msg'];

        try {
            $data = $this->twig->render('users/loginPage.html.twig', ['msg' => $msg]);
            $response->getBody()->write($data);
        } catch(LoaderError | SyntaxError | RuntimeError $e) {
            throw new TwigException($e->getMessage());
        }

        return $response;
    }


    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = (array)$request->getParsedBody();
        $login = filter_var($params['login'] ?? null, FILTER_SANITIZE_STRING);
        $password = filter_var($params['password'] ?? null, FILTER_SANITIZE_STRING);


        //TODO validation
        $validator = new Validator();

        $validator
            ->requirePresence('login', 'This field is required')
            ->requirePresence('password', 'This field is required')
            ->minLength('login', 3, 'Too short')
            ->maxLength('login', 60, 'Too long')
            ->minLength('password', 3, 'Too short')
            ->maxLength('password', 60, 'Too long')
        ;

        $errors = $validator->validate($params);
        if ($errors) {
            $msg = "";
            foreach ($errors as $field => $error) {
                foreach ($error as $check => $message) {
                    $msg .= "$field: $check $message; ";
                }
            }
            return $response->withHeader('Location', "/login?msg=$msg");
        }
        $repo = $this->em->getRepository(User::class);
        $user = $repo->findOneBy(['login' => $login]);
        if (!$user) {
            return $response->withHeader('Location', '/login?msg=Ошибка. Вы ввели неверные данные авторизации');
        }

        $saved_password_hash = $user->getPassword();
        if (password_verify($password, $saved_password_hash)) {
            $session = new \SlimSession\Helper();
            $session['user_id'] = $user->getId();
            $session['login'] = $login;
            return $response->withHeader('Location', '/');
        } else {
            return $response->withHeader('Location', '/login?msg=Ошибка. Вы ввели неверные данные авторизации');
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $session = new \SlimSession\Helper();
        $session::destroy();
        return $response->withHeader('Location', '/login');
    }

}
