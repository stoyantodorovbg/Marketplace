<?php


namespace AppBundle\Service;


use AppBundle\Entity\Currency;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Entity\UserProfile;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;

class UserService implements UserServiceInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function register(User $user, Form $form, $password)
    {

        $currencyRepo = $this->entityManager->getRepository(Currency::class);
        $defaultCurrency = $currencyRepo->find(1);

        $user->setPassword($password);

        $roleRepository = $this->entityManager->getRepository(Role::class);
        $userRepository = $this->entityManager->getRepository(User::class);

        $userRole = $roleRepository->findOneBy(['name' => 'ROLE_USER']);

        $userProfile = new UserProfile();
        $userProfile->setCurrency($defaultCurrency);
        $userProfile->setCash(1000);
        $userProfile->setIsSeller(0);
        $userProfile->setRating(1);

        $user->addRole($userRole);
        $user->addUserProfile($userProfile);

        $em = $this->entityManager;
        $em->persist($user);
        $em->flush();
    }
}