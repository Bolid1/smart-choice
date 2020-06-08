<?php

declare(strict_types=1);

namespace App\Controller\Company;

use App\DataPersister\InvitationDataPersister;
use App\Entity\Company;
use App\Entity\Invitation;
use App\Form\InvitationType;
use App\Security\CompanyVoter;
use App\Security\InvitationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/company/{company}/invitation")
 * @IsGranted("ROLE_USER")
 * @IsGranted(CompanyVoter::VIEW, subject="company")
 */
class InvitationController extends AbstractController
{
    /**
     * @Route("/new", name="invitation_new", methods={"GET","POST"})
     *
     * @param \App\Entity\Company $company
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\DataPersister\InvitationDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Company $company, Request $request, InvitationDataPersister $persister): Response
    {
        $invitation = (new Invitation())->setToCompany($company);

        $form = $this->createForm(InvitationType::class, $invitation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->isGranted(InvitationVoter::CREATE, $invitation)) {
            $persister->persist($invitation);

            return $this->redirectToRoute(
                'invitation_edit',
                ['id' => $invitation->getId(), 'company' => $company->getId()]
            );
        }

        return $this->render(
            'invitation/new.html.twig',
            [
                'company' => $company,
                'invitation' => $invitation,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="invitation_edit", methods={"GET","POST"})
     * @IsGranted(InvitationVoter::EDIT, subject="invitation")
     * @Security("invitation.getToCompany() === company")
     *
     * @param \App\Entity\Company $company
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Invitation $invitation
     * @param \App\DataPersister\InvitationDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Company $company, Request $request, Invitation $invitation, InvitationDataPersister $persister): Response
    {
        $form = $this->createForm(InvitationType::class, $invitation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $persister->persist($invitation);

            return $this->redirectToRoute('company_users_index', ['company' => $company->getId()]);
        }

        return $this->render(
            'invitation/edit.html.twig',
            [
                'company' => $company,
                'invitation' => $invitation,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/invitation/{id}", name="invitation_cancel", methods={"DELETE"})
     * @IsGranted(InvitationVoter::DELETE, subject="invitation")
     * @Security("invitation.getToCompany() === company")
     *
     * @param \App\Entity\Company $company
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Invitation $invitation
     * @param \App\DataPersister\InvitationDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(
        Company $company,
        Request $request,
        Invitation $invitation,
        InvitationDataPersister $persister
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$invitation->getId(), $request->request->get('_token'))) {
            $persister->remove($invitation);
        }

        return $this->redirectToRoute('company_users_index', ['company' => $company->getId()]);
    }
}
