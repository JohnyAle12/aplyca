<?php

namespace App\Controller;

use DateTime;
use App\Entity\Post;
use App\Form\PostType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PostController extends AbstractController
{
    public function create(): Response
    {
        $post = new Post;
        $form = $this->createForm(PostType::class, $post, [
            'action' => $this->generateUrl('post.store'),
            'method' => 'POST',
        ])->createView();
        
        return $this->render('post/index.html.twig', [
            'form' => $form
        ]);
    }

    public function store(Request $request, SluggerInterface $slugger){
        $em = $this->getDoctrine()->getManager();
        
        $publish_date = $request->get('post')['publish_date']['date'];
        $publish_time = $request->get('post')['publish_date']['time'];
        $date = $publish_date['year'].'-'.$publish_date['month'].'-'.$publish_date['day'].' '.$publish_time['hour'].':'.$publish_time['minute'].':00';
        $user = $this->getUser();
        
        $image = $request->files->get('post')['image'];
        if($image){
            $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

            try{
                $image->move(
                    $this->getParameter('post_images'),
                    $newFilename
                );

                $post = new Post;
                $post->setTitle($request->get('post')['title']);
                $post->setPublishDate(new DateTime($date));
                $post->setAuthor($request->get('post')['author']);
                $post->setImage($newFilename);
                $post->setContent($request->get('post')['content']);
                $post->setCreatedAt(new DateTime('now'));
                $post->setUpdatedAt(new DateTime('now'));
                $post->setUser($user);

                $em->persist($post);
                $em->flush();

                $this->addFlash('success', 'Post registrado con éxito');

                return $this->redirectToRoute('post.create');
            }catch(FileException $e) {
                //TODO Guardar un log del error generado
                throw new \Exception('Ah ocurrido un error en store()');
            }
        }
    }

    public function show(int $id): Response
    {
        $post = $this->getDoctrine()
            ->getRepository(Post::class)
            ->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No se encontro el post '.$id
            );
        }
        
        return new Response('Product name: '.$product->getName());
    }

    public function delete(Post $post): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();
    }
}
