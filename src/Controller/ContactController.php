<?php

namespace App\Controller;

use DateTime;
use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contact")
 */
class ContactController extends AbstractController
{
    
    public function index(ContactRepository $contactRepository): Response
    {
        return $this->render('contact/index.html.twig', [
            'contacts' => $contactRepository->findAll(),
        ]);
    }

    public function create(){
        $contact = new Contact;
        $form = $this->createForm(ContactType::class, $contact, [
            'action' => $this->generateUrl('contact.store'),
            'method' => 'POST',
        ])->createView();
        
        return $this->render('contact/create.html.twig', [
            'form' => $form
        ]);
    }

    
    public function store(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $contact = new Contact();
        $contact->setName($request->get('contact')['name']);
        $contact->setSubject($request->get('contact')['subject']);
        $contact->setMessage($request->get('contact')['message']);
        $contact->setCreatedAt(new DateTime('now'));

        $em->persist($contact);
        $em->flush();

        $this->addFlash('success', 'Información registrada con éxito');

        return $this->redirectToRoute('contact.create');
    }
}
