<?php

namespace App\Repository;

use App\Entity\UserActivity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserActivity>
 *
 * @method UserActivity|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserActivity|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserActivity[]    findAll()
 * @method UserActivity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserActivityRepository extends ServiceEntityRepository
{
    public $registry;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserActivity::class);
        $this->registry = $registry;
    }

//    /**
//     * @return UserActivity[] Returns an array of UserActivity objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserActivity
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function fetchAllActivities(){
        $em = $this->getEntityManager()->getConnection();
        $activities = $em->prepare("SELECT * FROM user_activity ORDER BY id DESC")->executeQuery()->fetchAllAssociative();
        return $activities;
    }
    public function editUserActivity($request){
        $entityManager = $this->registry->getManager();
        
          $activity = $entityManager->getRepository(UserActivity::class)->findOneBy(['id'=>$request->request->get('id')]);
        
          if (!$activity){
            return 404;
          }

          $activity->setDate($request->request->get('date'));
          $activity->setTitle($request->request->get('title'));
          $activity->setDescription($request->request->get('description'));
          $entityManager->flush();
          return 200;
        
    }

    public function fetchActivity($id){
        $em = $this->getEntityManager()->getConnection();
        $activity = $em->prepare("SELECT * FROM user_activity WHERE id = :id ORDER BY id DESC")->executeQuery(['id'=>$id])->fetchAllAssociative()[0];
        return $activity;
      }
}
