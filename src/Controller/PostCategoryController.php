<?php

namespace App\Controller;

use App\Entity\PostCategory;
use App\Repository\PostCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostCategoryController extends AbstractController
{
    #[Route('/api/admin/category/create', name: 'app_category_create')]
    public function create(Request $request, PostCategoryRepository $postCategoryRepository): JsonResponse
    {
        if (!array_key_exists('name', $request->toArray())) {
            return $this->json([
                'error' => 'Nome da categoria nao informado.',
            ]);
        }

        $category = new PostCategory();
        $category->setName($request->toArray()['name']);

        $postCategoryRepository->save($category, true);

        return $this->json([
            'success' => 'Categoria criada com sucesso.'
        ]);
    }

    #[Route('/api/categories', name: 'app_categories', methods: ['GET'])]
    public function listing(PostCategoryRepository $postCategoryRepository): JsonResponse
    {
        $categories = $postCategoryRepository->findAll();
        return $this->json([
            'categories' => array_map(fn (PostCategory $category) => [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'posts' => $category->getPosts()->toArray()
            ], $categories)
        ]);
    }

    #[Route('/api/admin/category/delete/{id}', name: 'app_category_delete', methods: ['DELETE'])]
    public function delete(int $id, PostCategoryRepository $postCategoryRepository, EntityManagerInterface $em): JsonResponse
    {
        $category = $em->getPartialReference(PostCategory::class, $id);
        $postCategoryRepository->remove($category, true);
        return $this->json([
            'success' => 'Categoria deletada com sucesso.'
        ]);
    }

    #[Route('/api/admin/category/update/{id}', name: 'app_category_update', methods: ['PUT'])]
    public function update(
        int $id,
        PostCategoryRepository $postCategoryRepository,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!array_key_exists('name', $request->toArray())) {
            return $this->json([
                'error' => 'Campo name nao informado.'
            ]);
        }

        $category = $postCategoryRepository->find($id);

        if (!$category) {
            return $this->json([
                'error' => 'categoria nao encontrada.'
            ]);
        }

        $category->setName($request->toArray()['name']);

        $em->flush();

        return $this->json([
            'success' => 'Categoria atualizada com sucesso.'
        ]);
    }


	#[Route('/api/category/show/{id}', name: 'app_category_show', methods: ['GET'])]
	public function show(int $id, PostCategoryRepository $postCategoryRepository): JsonResponse
    {
        $category = $postCategoryRepository->find($id);

        if (!$category) {
            return $this->json([
                'error' => 'Post nao encontrado'
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'id' => $category->getId(),
            'name' => $category->getName(),
            'posts' => $category->getPosts()->toArray()
        ]);
	}
}
