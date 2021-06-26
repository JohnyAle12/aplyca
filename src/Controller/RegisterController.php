<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use DateTime;

class RegisterController extends AbstractController
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function index(){
        $user = new User;
        $form = $this->createForm(UserType::class, $user, [
            'action' => $this->generateUrl('register.store'),
            'method' => 'POST',
        ])->createView();

        return $this->render('auth/register.html.twig', [
            'form' => $form
        ]);
    }

    public function store(Request $request){
        $em = $this->getDoctrine()->getManager();
        
        $user = new User;
        $user->setName($request->get('user')['name']);
        $user->setLastName($request->get('user')['lastname']);
        $user->setEmail($request->get('user')['email']);
        $user->setRoles(['ROLE_USER']);
        $user->setState(true);
        $user->setCreatedAt(new DateTime('now'));
        $user->setUpdatedAt(new DateTime('now'));

        $user->setPassword($this->passwordHasher->hashPassword(
            $user, $request->get('user')['password']
        ));

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Usuario registrado con éxito');

        return $this->redirectToRoute('login');
    }
}
