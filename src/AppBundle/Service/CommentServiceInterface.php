<?php


namespace AppBundle\Service;


use AppBundle\Entity\Comment;
use AppBundle\Entity\User;

interface CommentServiceInterface
{
    public function newAction(User $user, Comment $comment);

    public function deleteAction(Comment $comment, User $user);
}