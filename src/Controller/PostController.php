<?php

namespace App\Controller;

use DateTime;
use App\Entity\Post;
use App\Form\PostType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
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

    public function store(Request $request){
        $em = $this->getDoctrine()->getManager();
        
        $user = $this->getUser();
        $date = $this->parsePublishedDate($request->get('post')['publish_date']);
        $post = new Post;
        $post->setTitle($request->get('post')['title']);
        $post->setPublishDate(new DateTime($date));
        $post->setAuthor($request->get('post')['author']);
        $post->setContent($request->get('post')['content']);
        $post->setCreatedAt(new DateTime('now'));
        $post->setUpdatedAt(new DateTime('now'));
        $post->setUser($user);

        $newFilename = $this->storeFile($request->files);
        if($newFilename){
            $post->setImage($newFilename);
        }

        $em->persist($post);
        $em->flush();

        $this->addFlash('success', 'Post registrado con éxito');

        return $this->redirectToRoute('post.create');
    }

    public function edit(Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post, [
            'action' => $this->generateUrl('post.update', ['post' => $post->getId()]),
            'method' => 'PUT',
        ])->createView();
        
        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    public function update(Request $request, Post $post){
        $em = $this->getDoctrine()->getManager();
        
        $date = $this->parsePublishedDate($request->request->get('post')['publish_date']);
        $post->setTitle($request->request->get('post')['title']);
        $post->setPublishDate(new DateTime($date));
        $post->setAuthor($request->request->get('post')['author']);
        $post->setContent($request->request->get('post')['content']);
        $post->setUpdatedAt(new DateTime('now'));

        $newFilename = $this->storeFile($request->files);
        if($newFilename){
            $post->setImage($newFilename);
        }

        $em->persist($post);
        $em->flush();

        $this->addFlash('success', 'Post actualizado con éxito');

        return $this->redirectToRoute('post.edit', ['post' => $post->getId()]);
    }

    public function show(Post $post): Response
    {
        if (!$post) {
            throw $this->createNotFoundException(
                'No se encontro el post '.$id
            );
        }
        
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    public function delete(Post $post): Response
    {       
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        $filesystem = new Filesystem();
        $path = $this->getParameter('post_images').'/'.$post->getImage();
        $filesystem->remove($path);

        $this->addFlash('success', 'Post eliminado con éxito');

        return $this->redirectToRoute('home');
    }

    private function parsePublishedDate($request){
        $publish_date = $request['date'];
        $publish_time = $request['time'];
        return $publish_date['year'].'-'.$publish_date['month'].'-'.$publish_date['day'].' '.$publish_time['hour'].':'.$publish_time['minute'].':00';
    }

    private function storeFile($file){
        try{
            $image = $file->get('post')['image'];
            if($image){
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = uniqid().'.'.$image->guessExtension();
                
                $image->move(
                    $this->getParameter('post_images'),
                    $newFilename
                );
                return $newFilename;
            }
            return null;
        }catch(FileException $e) {
            throw new \Exception('Ah ocurrido un error en storeFile()');
        }
    }
}
