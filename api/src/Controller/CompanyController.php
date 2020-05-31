<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use App\DataPersister\CompanyDataPersister;
use App\Security\CompanyExtension;
use App\Entity\Company;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use App\Security\CompanyVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/companies")
 * @IsGranted("ROLE_USER")
 */
class CompanyController extends AbstractController
{
    /**
     * @Route("/", name="companies_list", methods={"GET"})
     *
     * @param CompanyRepository $repository
     * @param CompanyExtension $extension
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(CompanyRepository $repository, CompanyExtension $extension): Response
    {
        $extension->applyToCollection(
            $queryBuilder = $repository->createQueryBuilder('company'),
            new QueryNameGenerator(),
            Company::class,
            'get'
        );

        return $this->render(
            'company/list.html.twig',
            [
                'companies' => $queryBuilder->getQuery()->execute(),
            ]
        );
    }

    /**
     * @Route("/new", name="companies_new", methods={"GET","POST"})
     * @IsGranted(CompanyVoter::PRE_CREATE)
     *
     * @param Request $request
     * @param CompanyDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request, CompanyDataPersister $persister): Response
    {
        $company = new Company();
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $persister->persist($company);

            return $this->redirectToRoute('companies_list');
        }

        return $this->render(
            'company/new.html.twig',
            [
                'company' => $company,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="companies_show", methods={"GET"})
     * @IsGranted(CompanyVoter::VIEW, subject="company")
     *
     * @param Company $company
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Company $company): Response
    {
        return $this->render(
            'company/show.html.twig',
            [
                'company' => $company,
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="companies_edit", methods={"GET","POST"})
     * @IsGranted(CompanyVoter::EDIT, subject="company")
     *
     * @param Request $request
     * @param Company $company
     * @param CompanyDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, Company $company, CompanyDataPersister $persister): Response
    {
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $persister->persist($company);

            return $this->redirectToRoute('companies_list');
        }

        return $this->render(
            'company/edit.html.twig',
            [
                'company' => $company,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="companies_delete", methods={"DELETE"})
     * @IsGranted(CompanyVoter::DELETE, subject="company")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Company $company
     * @param \App\DataPersister\CompanyDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Company $company, CompanyDataPersister $persister): Response
    {
        if ($this->isCsrfTokenValid('delete'.$company->getId(), $request->request->get('_token'))) {
            $persister->remove($company);
        }

        return $this->redirectToRoute('companies_list');
    }
}
