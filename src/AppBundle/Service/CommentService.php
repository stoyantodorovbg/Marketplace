<?php


namespace AppBundle\Service;


use AppBundle\Entity\Comment;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;

class CommentService implements CommentServiceInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function newAction(User $user, Comment $comment)
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        $em = $this->entityManager;
        $em->persist($comment);
        $em->flush();

        $userProfile = $user->getUserProfile();
        $userProfile->setRating($userProfile->getRating() + 0.02);
        $em->persist($userProfile);
        $em->flush();

        $connection->commit();
    }

    public function deleteAction(Comment $comment, User $user)
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        $em = $this->entityManager;
        $em->remove($comment);
        $em->flush();

        $userProfileAuthor = $user->getUserProfile();
        $userProfileAuthor->setRating($userProfileAuthor->getRating() - 0.1);
        $em->remove($userProfileAuthor);
        $em->flush();

        $connection->commit();
    }

}