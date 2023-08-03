<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    public $userRepo;

    public function __construct(UserRepository $userRepo)
    {
       $this->userRepo = $userRepo; 
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    
    #[Route('/register_user', name: 'app_register_user', methods:['POST'])]
    public function registerUSer(Request $request, EntityManagerInterface $entityManager)
    {
        if ($request->request->get('email') == null || $request->request->get('name')==null || $request->request->get('password')==null) {
            return $this->json(['message'=>'email, name & password must be set', 'success'=>false], 401);
        }
        $users = $entityManager->getRepository(User::class)->findBy(['email'=>$request->request->get('email')]);
        if($users){
            return $this->json(['message'=>$request->request->get('email'), 'success'=>false], 400);
        }

        $new_user = $this->userRepo->createUser($request);
        if($new_user == 1){
            return $this->json(['payload'=>['name'=>$request->request->get('name'), 'email'=>$request->request->get('email')], 'success'=>true], 201);
        }else{
            return $this->json(['message'=>'something went wrong... please try again later or contact system admin', 'success'=>false], 500);
        }

    }
}
