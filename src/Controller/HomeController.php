<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Movie;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
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

        $current_date = new DateTime();

        try {
            $data = $this->twig->render('home/index.html.twig', [
                'trailers' => $this->fetchData(),
                'current_controller' => $current_class_name,
                'current_method' => $current_method_name,
                'current_date' => $current_date->format('Y-m-d H:i:s'),
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
        $data = $this->em->getRepository(Movie::class)
            ->findAll();

        return new ArrayCollection($data);
    }
}
