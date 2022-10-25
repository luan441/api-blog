<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

class UserController extends AbstractController
{
    #[Route('/api/admin/users', name: 'app_user_listing', methods: ['GET'])]
    public function listing(UserRepository $userRepository): JsonResponse
    {
        return $this->json($userRepository->findAll());
    }

    #[Route('/api/admin/user/create', name: 'app_user_create', methods: ['POST'])]
    public function create(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): JsonResponse
    {
        $data = $request->toArray();

        $validate = $this->validateInput($data);

        if ($validate) {
            return $this->json([
                'errors' => $validate
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = (new User())->setFromAnArray($data);

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $data['password']
        );

        $user->setPassword($hashedPassword);

        $userRepository->save($user, true);

        return $this->json(['success' => 'Usuario criado com sucesso']);
    }

    #[Route('/api/admin/user/delete/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): JsonResponse
    {
        $user = $em->getPartialReference(User::class, $id);
        $userRepository->remove($user, true);

        return $this->json(['success' => 'Usuario Deletado com sucesso']);    
    }
    
    #[Route('/api/admin/user/update/{id}',name: 'app_user_update', methods: ['PUT'])]
    public function update(int $id, UserRepository $userRepository, Request $request,EntityManagerInterface $em): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json([
                'error' => 'Usuario nao econtrado.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $user->setFromAnArray($request->toArray());

        $em->flush();

        return $this->json([
            'Usuario atualizado com sucesso.'
        ]);
    }

    #[Route('/api/admin/user/show/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(
        int $id,
        UserRepository $userRepository,
    ): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json([
                'error' => 'Usuario nao econtrado.'
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($user);
    }

    private function validateInput(array $input): string | bool
    {
        $validate = Validation::createValidator();

        $constrains = new Assert\Collection([
            'name' => new Assert\Type('string'),
            'username' => new Assert\Type('string'),
            'email' => new Assert\Type('string'),
            'password' => new Assert\Type('string'),
            'roles' => new Assert\Type('array'),
        ]);

        $vaiolation = $validate->validate($input, $constrains);

        if (count($vaiolation) <= 0) {
            return false;  
        }

        return (string) $vaiolation;
    } 
}
