<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use \Firebase\JWT\JWT; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use UnexpectedValueException;

class AuthController extends AbstractController
{
    #[Route(path: '/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/login', name: 'app_user_login', methods:['POST'])]
    public function userLogin(Request $request, EntityManagerInterface $em){
        $user = $em->getRepository(User::class)->findOneBy(['email'=>$request->get('email')]);
        if (! $user){
            return $this->json(['message'=>'account not found'.$request->get('email')], 500);
        }
        if (password_verify($request->request->get('password'), $user->getPassword()))
        {
            $payload = array(
                'iss' => $_SERVER['HOST_NAME'],
                'exp' => time()+3600, 'name'=> $user->getName(),
                'email'=>$request->request->get('email'), 'id'=>$user->getId()

                ); 
                try{
                    $jwt = JWT::encode($payload, $this->getParameter('app.passphrase'),'HS256'); 
                    $res=array("success"=>true,"Token"=>$jwt);
                    }catch (UnexpectedValueException $e) {
                    $res=array("status"=>false,"Error"=>$e->getMessage());
                    }
                
        }
        return $this->json($res, 200);
    }
}
