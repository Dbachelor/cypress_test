<?php

namespace App\Repository;

use App\Entity\Activity;
use App\Entity\UserActivity;
use App\Service\Files\FileUploadService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activity>
 *
 * @method Activity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activity[]    findAll()
 * @method Activity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityRepository extends ServiceEntityRepository
{
    public $registry;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
        $this->registry = $registry;
       
    }

//    /**
//     * @return Activity[] Returns an array of Activity objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Activity
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

      public function fetchAllUserActivities($user_id){
        $em = $this->getEntityManager()->getConnection();
        $activities = $em->prepare("SELECT t1.*
        FROM activity t1
        LEFT JOIN user_activity t2 ON t2.activity_id = t1.id
        WHERE t2.activity_id IS NULL")->executeQuery()->fetchAllAssociative();
        $user_activities = $em->prepare("SELECT user_activity.*, user.name as name FROM `user_activity` LEFT JOIN user ON user_activity.created_for = user.id WHERE created_for=:user_id")->executeQuery(["user_id"=>$user_id])->fetchAllAssociative();
        return [$activities, $user_activities];
      }

      public function fetchAllUserActivitiesWithinRange($user_id, $date_range){
        $em = $this->getEntityManager()->getConnection();
        $activities = $em->prepare("SELECT t1.*
        FROM activity t1
        LEFT JOIN user_activity t2 ON t2.activity_id = t1.id
        WHERE t2.activity_id IS NULL")->executeQuery()->fetchAllAssociative();
        $user_activities = $em->prepare("SELECT user_activity.*, user.name as name FROM `user_activity` LEFT JOIN user ON user_activity.created_for = user.id WHERE created_for=:user_id")->executeQuery(["user_id"=>$user_id])->fetchAllAssociative();
        return array_merge($activities, $user_activities);
      }

      public function fetchAllActivities(){
        $em = $this->getEntityManager()->getConnection();
        $activities = $em->prepare("SELECT *
        FROM activity ")->executeQuery()->fetchAllAssociative();
        $user_activities = $em->prepare("SELECT user_activity.*, user.name FROM `user_activity` LEFT JOIN user ON user_activity.created_for = user.id")->executeQuery()->fetchAllAssociative();
        return [$activities, $user_activities];
      }
      public function fetchActivity($id){
        $em = $this->getEntityManager()->getConnection();
        $activity = $em->prepare("SELECT * FROM activity WHERE id = :id ORDER BY id DESC")->executeQuery(['id'=>$id])->fetchAllAssociative()[0];
        return $activity;
      }

      public function createActivity($request){
        $entity = $this->registry->getManager();
        if ($request->request->get('created_for') > 0){
          $activity = new UserActivity();
        }else{
          $activity = new Activity();
        }
        $file = new FileUploadService();
        $image = explode('public/', $file->upload('image')['filename'])[1];
        $activity->setImagePath($image);
        $activity->setTitle($request->request->get('title'));
        $activity->setCreatedFor($request->request->get('created_for'));
        $activity->setDate($request->request->get('date'));
        $activity->setDescription($request->request->get('description'));
        $activity->setCreatedBy(1);
        if ($request->request->get('activity_id') != null){
          $activity->setActivityId($request->request->get('activity_id'));
        }
        $entity->persist($activity);
        $entity->flush();
        return 1;
      }

      public function editActivity($request){
        $entityManager = $this->registry->getManager();
        $res = 0;
        if ($request->request->get('created_for') == 0 && $request->request->get('type') == 0){
          $activity = $entityManager->getRepository(Activity::class)->findOneBy(['id'=>$request->request->get('activity_id')]);
          //echo 1;
          if (!$activity){
            return  404;
          }

          $activity->setDate($request->request->get('date'));
          $activity->setTitle($request->request->get('title'));
          $activity->setDescription($request->request->get('description'));
          $entityManager->flush();
          return  200;
        }
        else if ($request->request->get('created_for') > 0 && $request->request->get('type') == 0){
          //echo 2;
          $new_activity = new UserActivity();
          $new_activity->setTitle($request->request->get('title'));
          $new_activity->setDescription($request->request->get('description'));
          $new_activity->setCreatedFor($request->request->get('created_for'));
          $new_activity->setDate($request->request->get('date'));
          $new_activity->setActivityId($request->request->get('activity_id'));
          $new_activity->setCreatedBy(1);
          $entityManager->persist($new_activity);
          $entityManager->flush();
          return  200;
          }else if ($request->request->get('created_for') > 0 && $request->request->get('type') > 0){
       
            $activity = $entityManager->getRepository(UserActivity::class)->findOneBy(['id'=>$request->request->get('activity_id')]);
            if ($activity){
        
              $activity->setDate($request->request->get('date'));
              $activity->setTitle($request->request->get('title'));
              $activity->setDescription($request->request->get('description'));
              $entityManager->flush();
              $res = 200;
            }
          else{
        
            $new_activity = new UserActivity();
            $new_activity->setTitle($request->request->get('title'));
            $new_activity->setDescription($request->request->get('description'));
            $new_activity->setCreatedFor($request->request->get('created_for'));
            $new_activity->setDate($request->request->get('date'));
            $new_activity->setActivityId($request->request->get('activity_id'));
            $new_activity->setCreatedBy(1);
            $entityManager->persist($new_activity);
            $entityManager->flush();
            return 200;
          }
        }
        //die(json_encode($request->request->all()));
        return $res;

      }



}
