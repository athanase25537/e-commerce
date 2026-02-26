<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/category")]
final class CategoryController extends AbstractController
{
    #[Route('/', name: 'app_category')]
    public function index(): Response
    {
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
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
        }

        return $this->render('category/index.html.twig', [
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
            return $this->redirect('app_category_new');
        }

        return $this->render('category/update.html.twig', [
            'form' => $categoryForm
        ]);
    }
}
