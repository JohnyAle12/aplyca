<?php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;


class HomeController extends AbstractController
{
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $posts = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findAll();
        
        $pagination = $paginator->paginate(
            $posts,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('home/index.html.twig', [
            'posts' => $pagination,
        ]);
    }

    public function home(Request $request, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser()->getId();
        $posts = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findByUserCreated($user);

        $pagination = $paginator->paginate(
            $posts,
            $request->query->getInt('page', 1),
            10
        );

        $total = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findTotalByUserCreated($user);
        
        $totalToday = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findTotalByUserCreated($user, date('Y-m-d'));

        return $this->render('home/home.html.twig', [
            'posts' => $posts,
            'total' => $total[0][1],
            'totalToday' => $totalToday[0][1],
            'pagination' => $pagination
        ]);
    }


}
