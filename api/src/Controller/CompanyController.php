<?php

declare(strict_types=1);

namespace App\Controller;

use App\DataPersister\CompanyDataPersister;
use App\Entity\Company;
use App\Form\CompanyType;
use App\Security\CompanyVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/company")
 * @IsGranted("ROLE_USER")
 */
class CompanyController extends AbstractController
{
    /**
     * @Route("/new", name="company_new", methods={"GET","POST"})
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

            return $this->redirectToRoute('company_dashboard', ['company' => $company->getId()]);
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
     * @Route("/{id}/edit", name="company_edit", methods={"GET","POST"})
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

            return $this->redirectToRoute('company_dashboard', ['company' => $company->getId()]);
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
     * @Route("/{id}", name="company_delete", methods={"DELETE"})
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

        return $this->redirectToRoute('app_index');
    }
}
