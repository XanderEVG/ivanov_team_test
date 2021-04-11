<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Movie;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use ReflectionException;
use Slim\Exception\HttpBadRequestException;
use Slim\Interfaces\RouteCollectorInterface;
use Twig\Environment;

/**
 * Class HomeController.
 */
class HomeController
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
     *
     * @throws HttpBadRequestException
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Получаем имя класса и метод
        try {
            $modelReflector = new ReflectionClass(__CLASS__);
            $current_class_name = $modelReflector->getShortName();
            $current_method_name = $modelReflector->getMethod('index')->getName();
        } catch (ReflectionException $e) {
            $current_class_name = "Не определен";
            $current_method_name = "не определен";
        }

        // Получаем текущую дату
        $current_date = new DateTime();

        // Получаем имя пользователя, если он залогинен
        $session = new \SlimSession\Helper();
        $login = $session['login'] ?? null;

        // Рендерим шаблон
        try {
            $data = $this->twig->render('home/index.html.twig', [
                'trailers' => $this->fetchData(),
                'current_controller' => $current_class_name,
                'current_method' => $current_method_name,
                'current_date' => $current_date->format('Y-m-d H:i:s'),
                'login' => $login,
            ]);
        } catch (\Exception $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        // Пишем собранный шаблон в респонс
        $response->getBody()->write($data);

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     *
     * @throws HttpBadRequestException
     * @throws Exception
     */
    public function trailer(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        //$trailer_id = $request->getQueryParam('trailer_id'); // В 4 версии такого метода нет WTF?

        // Получаем параметры
        $params = $request->getQueryParams();
        $trailer_id = intval($params['id']);
        if ($trailer_id == 0) {
            throw new Exception("Не задан обязательный параметр");
        }

        // Получаем имя пользователя, если он залогинен
        $session = new \SlimSession\Helper();
        $login = $session['login'] ?? null;

        // Ищем трейлер
        $repo = $this->em->getRepository(Movie::class);
        $trailer = $repo->find($trailer_id);
        if(!$trailer) {
            $response->getBody()->write("Такой трейлер не найден");
            return $response->withStatus(404);
        }

        // Собираем шаблон и отправляем данные
        try {
            $data = $this->twig->render('home/trailer.html.twig', [
                'trailer' => $trailer,
                'login' => $login,
            ]);
        } catch (\Exception $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        $response->getBody()->write($data);

        return $response;
    }

    /**
     * @return Collection
     */
    protected function fetchData(): Collection
    {
        $repo = $this->em->getRepository(Movie::class);
        $data = $repo->findAll();
        return new ArrayCollection($data);
    }
}
