<?php
namespace App\Test\TestCase\Support;

use App\Entity\Movie;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class LikesTest extends TestCase
{
    public function testAdd(): void
    {
        $movie = new Movie();
        $movie->addLike(new User());
        $result = $movie->getLikes();
        $this->assertEquals(1, count($result));
    }
}