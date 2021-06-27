<?php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
    public function index(): Response
    {
        $posts = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findAll();

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    public function home(): Response
    {
        $user = $this->getUser()->getId();
        $posts = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findByUserCreated($user);

        $total = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findTotalByUserCreated($user);
        
        $totalToday = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findTotalByUserCreated($user, date('Y-m-d'));

        return $this->render('home/home.html.twig', [
            'posts' => $posts,
            'total' => $total[0][1],
            'totalToday' => $totalToday[0][1]
        ]);
    }


}
