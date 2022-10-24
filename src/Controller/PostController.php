<?php

namespace App\Controller;

use App\Entity\Post;
use App\Helper\Enums\Post\Status;
use App\Repository\PostCategoryRepository;
use App\Repository\PostRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class PostController extends AbstractController
{
    #[Route('/api/posts', name: 'app_post_listing', methods: ['GET'])]
    public function listing(
        PostRepository $postRepository
    ): JsonResponse {
        return $this->json([
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/api/admin/post/create', name: 'app_post_create', methods: ['POST'])]
    public function FunctionName(
        Request $request,
        PostCategoryRepository $postCategoryRepository,
        PostRepository $postRepository
    ): JsonResponse {
        $data = $request->toArray();
        $validate = $this->validateInput($data);

        if ($validate) {
            return $this->json([
                'errors' => $validate,
            ], Response::HTTP_BAD_REQUEST);
        }

        $post = new Post();
        $post
            ->setTitle($data['title'])
            ->setDescription($data['description'])
            ->setStatus(Status::from($data['status']))
            ->setContent($data['content'])
            ->setDate(new DateTimeImmutable($data['date']));

        foreach ($data['categories'] as $categoryId) {
            $category = $postCategoryRepository->find($categoryId);

            if ($category) {
                $post->addCategory($category);
            }
        }

        $postRepository->save($post, true);

        return $this->json([
            'success' => 'Post criado com sucesso'
        ]);
    }

    #[Route('/api/admin/post/update/{id}', name: 'app_post_update', methods: ["PUT"])]
    public function update(
        int $id,
        Request $request,
        PostRepository $postRepository,
        PostCategoryRepository $postCategoryRepository,
        EntityManagerInterface $em,
    ): JsonResponse {
        $data = $request->toArray();

        if (array_key_exists('categories', $data)) {
            $data['categories'] = $postCategoryRepository->findBy(['id' => $data['categories']]);
        }

        $post = $postRepository->find($id);

        $post->setFromAnArray($data);

        $em->flush();

        return $this->json([
            'success' => 'Atualizado com sucesso.'
        ]);
    }
		
    #[Route('/api/admin/post/delete/{id}', name: 'app_post_delete', methods: ['DELETE'])]
    public function delete(int $id, PostRepository $postRepository, EntityManagerInterface $em): JsonResponse
    {
        $post = $em->getPartialReference(Post::class, $id);
        $postRepository->remove($post, true);
        return $this->json([
            'success' => 'Post deletado com sucesso.'
        ]);
    }

	#[Route('/api/post/show/{id}', name: 'app_post_show', methods: ['GET'])]
	public function show(int $id, PostRepository $postRepository): JsonResponse
    {
        $post = $postRepository->find($id);

        if (!$post) {
            return $this->json([
                'error' => 'Post nao encontrado'
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($post);
	}

    private function validateInput(array $input): string | bool
    {
        $validator = Validation::createValidator();

        $constraints = new Assert\Collection([
            'title' => new Assert\Length(['max' => 255]),
            'description' => new Assert\Length(['max' => 255]),
            'categories' => new Assert\Type('array'),
            'status' => new Assert\Choice(['draft', 'published']),
            'content' => new Assert\Type('string'),
            'date' => new Assert\Date(),
        ]);

        $group = new Assert\GroupSequence(['Default']);

        $violation = $validator->validate($input, $constraints, $group);

        if (count($violation) <= 0) {
            return false;
        }

        return (string) $violation;
    }
}
