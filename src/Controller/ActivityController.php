<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\UserActivity;
use App\Repository\ActivityRepository;
use App\Repository\UserActivityRepository;
use App\Repository\UserRepository;
use App\Service\Files\FileUploadService;
use App\Service\JWTAuth\JWTAuth;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivityController extends AbstractController
{
    public $activityRepo, $userRepo, $userActivityRepo, $file;

    public function __construct(UserRepository $userRepo, ActivityRepository $activityRepo, UserActivityRepository $userAccRepo){
        $this->userRepo = $userRepo;
        $this->activityRepo = $activityRepo;
        $this->userActivityRepo = $userAccRepo;
        // $this->file = $file;
    }

    #[Route('/activity', name: 'app_activity', methods:['GET'])]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE_ADMIN');
        $users = $this->userRepo->findBy(['is_admin'=>0]);
        $count_todays_activity = $this->activityRepo->findBy(['date'=>date('Y-m-d')]);
        return $this->render('activity/index.html.twig', [
            'users' => $users,
            'count_activity'=> count($count_todays_activity)
        ]);
    }

    #[Route('/user_activity', name: 'user_activity')]
    public function userActivity(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE_ADMIN');
        $user_activity = $this->activityRepo->fetchAllUserActivities($_GET['id']);
       
        return $this->render('activity/user_activity.html.twig', [
            'user_activities' => $user_activity[1],
            'general_activities' => $user_activity[0],
            'name'=> $user_activity[1][0]['name'],
        ]);
    }

    #[Route('/activity', name: 'post_activity', methods:['POST'])]
    public function postActivity(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE_ADMIN');
        $activity = $this->activityRepo->createActivity($request);
        $this->addFlash('message', 'new activity has been added');
        return $this->redirect("activity");
    }

    #[Route('/list_activity', name: 'list_activity', methods:['GET'])]
    public function getActivities(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE_ADMIN');
        $activity = $this->activityRepo->fetchAllActivities();
        $users = $this->userRepo->findBy(['is_admin'=>0]);
        return $this->render('activity/activities.html.twig', [
            'general_activities' => $activity[0],
            'user_activities'=> $activity[1],
            'users'=>$users,
        ]);
    }  
    
    #[Route('/get_activity', name: 'get_activity', methods:['GET'])]
    public function getActivity(Request $request): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE_ADMIN');
        if($_GET['type'] == 0){
            $activity = $this->activityRepo->fetchActivity($_GET['id']);
        }else{
            $activity = $this->userActivityRepo->fetchActivity($_GET['id']); 
        }
        
        $users = $this->userRepo->findBy(['is_admin'=>0]);
        return $this->json($activity, 200);
    }

    #[Route('/get_users_activity', name: 'get_users_activity', methods:['GET'])]
    public function getUserActivity(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE_ADMIN');
        $activity = $this->userActivityRepo->fetchActivity($_GET['id']);
        $users = $this->userRepo->findBy(['is_admin'=>0]);
        return $this->json($activity, 200);
    }
    
    #[Route('/user_activity_range', name: 'get_user_activity', methods:['GET'])]
    public function userActivityInRange(Request $request)
    {
        $authencticate = JWTAuth::validateToken(JWTAuth::getHeaderToken());
        //print_r($authencticate);
        if (!$authencticate['status'] == true){
            return $this->json(['message'=>'access denied'], 401);
        }
        $activities = $this->activityRepo->fetchAllUserActivitiesWithinRange($authencticate[0]['sub'], $_GET['from'], $_GET['to']);
        return $this->json($activities, 200);
    }

    #[Route('/total_activities', name: 'count_activity', methods:['GET'])]
    public function countActivities(Request $request)
    {
        $total = $this->activityRepo->findBy(['date'=>$_GET['date']]);
        return $this->json(['total'=> count($total)], 200);
    }

    #[Route('/users_activities', name: 'list_users_activities', methods:['GET'])]
    public function usersActivities(Request $request)
    {
        //$authencticate = JWTAuth::validateToken(JWTAuth::getHeaderToken(), $this->getParameter('passphrase'));
        // if (!$authencticate['status'] == true){
        //     return $this->json(['message'=>'access denied'], 401);
        // }
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE_ADMIN');
        $activities = $this->userActivityRepo->fetchAllActivities();
        $users = $this->userRepo->findBy(['is_admin'=>0]);
        return $this->render('activity/users_activity.html.twig', [
            'activities' => $activities,
            'users'=>$users,
        ]);
        
    }

    #[Route('/edit_activity', name: 'edit_activity', methods:['POST'])]
    public function editActivity(Request $request){
        $edited = $this->activityRepo->editActivity($request);
        if ($edited == 404){
            $this->addFlash(
                'message',
                'Activity Not Found'
            );
            
        }

        if ($edited == 200){
            $this->addFlash('message', 'Activity Edited');
             
        }
        return $this->redirectToRoute('list_activity');
    }

    #[Route('/edit_user_activity', name: 'edit_user_activity', methods:['POST'])]
    public function editUserActivity(Request $request){
        $edited = $this->userActivityRepo->editUserActivity($request);
        if ($edited == 404){
            $this->addFlash(
                'message',
                'Activity Not Found'
            );
            
        }

        if ($edited == 200){
            $this->addFlash('message', 'Activity Edited');
             
        }
        return $this->redirectToRoute('list_users_activities');
    }

    #[Route('/delete_activity', name: 'delete_activity', methods:['GET'])]
    public function deleteActivity(EntityManagerInterface $entityManager){
        $id = $_GET['id'];
        $type=$_GET['type'];
        if ($type==0){
            $activity = $entityManager->getRepository(Activity::class)->find($id);
        }else{
            $activity = $entityManager->getRepository(UserActivity::class)->find($id); 
        }

        if (!$activity) {
            throw $this->createNotFoundException(
                'No activity found for id '.$id
            );
        }
        $entityManager->remove($activity);
        $entityManager->flush();
        return $this->json(['message'=>'activity has been deleted'], 200);
    }
       
}
