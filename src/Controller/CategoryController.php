<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/category")]
final class CategoryController extends AbstractController
{
    #[Route('/', name: 'app_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'app_category_new')]
    public function addCategory(Request $request, EntityManagerInterface $em): Response
    {

        $categoryForm = $this->createForm(CategoryType::class);
        $categoryForm->handleRequest($request);
        if($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            $em->persist($categoryForm->getData());
            $em->flush();

            $this->addFlash('success', 'Categorie bien enregistrer !');
            return $this->redirectToRoute('app_category');
        }

        return $this->render('category/new.html.twig', [
            'form' => $categoryForm
        ]);
        
    }

    #[Route('/{id}/update', name: 'app_category_update')]
    public function updateCategory(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        $categoryForm = $this->createForm(CategoryType::class, $category);
        $categoryForm->handleRequest($request);
        if($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Categorie bien mis a jour !');
            return $this->redirectToRoute('app_category');
        }

        return $this->render('category/update.html.twig', [
            'form' => $categoryForm
        ]);
    }

    #[Route('/{id}/delete', name: 'app_category_delete', methods: ['POST'])]
    public function deleteCategory(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete_category_' . $category->getId(), $token)) {
            $em->remove($category);
            $em->flush();

            $this->addFlash('success', 'Categorie supprimee !');
        } else {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('app_category');
    }
}
