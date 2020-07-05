<?php

declare(strict_types=1);

namespace App\Controller\Company;

use App\Entity\Category;
use App\Entity\Company;
use App\Form\CategoryType;
use App\Security\CompanyVoter;
use App\Security\Voter\CategoryVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/company/{company}")
 * @IsGranted("ROLE_USER")
 * @IsGranted(CompanyVoter::VIEW, subject="company")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/categories", name="company_categories", methods={"GET"})
     *
     * @param \App\Entity\Company $company
     * @param \App\Repository\CategoryRepository $categoryRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(Company $company): Response
    {
        return $this->render(
            'category/list.html.twig',
            [
                'company' => $company,
                'categories' => $company->getCategories(),
            ]
        );
    }

    /**
     * @Route("/category/new", name="category_new", methods={"GET","POST"})
     * @IsGranted(CategoryVoter::PRE_CREATE, subject="company")
     *
     * @param \App\Entity\Company $company
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Company $company, Request $request): Response
    {
        $category = new Category();
        $category->company = $company;

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->isGranted(CategoryVoter::CREATE, $category)) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('company_categories', ['company' => $company->getId()]);
        }

        return $this->render(
            'category/new.html.twig',
            [
                'company' => $company,
                'category' => $category,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/category/{id}/edit", name="category_edit", methods={"GET","POST"})
     * @IsGranted(CategoryVoter::EDIT, subject="category")
     * @Security("category.company === company")
     *
     * @param \App\Entity\Company $company
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Category $category
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Company $company, Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('company_categories', ['company' => $company->getId()]);
        }

        return $this->render(
            'category/edit.html.twig',
            [
                'company' => $company,
                'category' => $category,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/category/{id}", name="category_delete", methods={"DELETE"})
     * @IsGranted(CategoryVoter::DELETE, subject="category")
     * @Security("category.company === company")
     *
     * @param \App\Entity\Company $company
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Category $category
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Company $company, Request $request, Category $category): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('company_categories', ['company' => $company->getId()]);
    }
}
