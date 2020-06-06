<?php

namespace App\Controller\Company;

use App\DataPersister\RightDataPersister;
use App\Entity\Company;
use App\Form\RightType;
use App\Entity\Right;
use App\Security\RightVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/company/{company}/right")
 * @IsGranted("ROLE_USER")
 */
class RightController extends AbstractController
{
    /**
     * @Route("/{user}", name="right_show", methods={"GET"})
     * @IsGranted(RightVoter::VIEW, subject="right")
     * @Security("right.getCompany() === company")
     *
     * @param Company $company
     * @param Right $right
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Company $company, Right $right): Response
    {
        return $this->render(
            'right/show.html.twig',
            [
                'company' => $company,
                'right' => $right,
            ]
        );
    }

    /**
     * @Route("/{user}/edit", name="right_edit", methods={"GET","POST"})
     * @IsGranted(RightVoter::EDIT, subject="right")
     * @Security("right.getCompany() === company")
     *
     * @param Company $company
     * @param Request $request
     * @param Right $right
     * @param RightDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Company $company, Request $request, Right $right, RightDataPersister $persister): Response
    {
        $form = $this->createForm(RightType::class, $right);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $persister->persist($right);
        }

        return $this->render(
            'right/edit.html.twig',
            [
                'company' => $company,
                'right' => $right,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{user}", name="right_delete", methods={"DELETE"})
     * @IsGranted(RightVoter::DELETE, subject="right")
     * @Security("right.getCompany() === company")
     *
     * @param Company $company
     * @param Request $request
     * @param Right $right
     * @param RightDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Company $company, Request $request, Right $right, RightDataPersister $persister): Response
    {
        $id = $right->getId();
        $csrfKey = $id ? \implode('', ['delete', ...\array_values($id)]) : null;

        if ($csrfKey && $this->isCsrfTokenValid($csrfKey, $request->request->get('_token'))) {
            $persister->remove($right);
        }

        return $this->redirectToRoute('company_users_index', ['company' => $company->getId()]);
    }
}
