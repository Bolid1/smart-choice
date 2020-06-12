<?php

declare(strict_types=1);

namespace App\Controller\Company;

use App\DataPersister\InvitationDataPersister;
use App\Entity\Company;
use App\Entity\Invitation;
use App\Entity\User;
use App\Form\InvitationAcceptType;
use App\Form\InvitationType;
use App\Security\CompanyVoter;
use App\Security\InvitationVoter;
use App\Service\InvitationAcceptor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/company/{company}/invitation")
 * @IsGranted("ROLE_USER")
 */
class InvitationController extends AbstractController
{
    /**
     * @Route("/new", name="invitation_new", methods={"GET","POST"})
     * @IsGranted(CompanyVoter::VIEW, subject="company")
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
     * @IsGranted(CompanyVoter::VIEW, subject="company")
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
     * @Route("/{id}/accept", name="invitation_accept", methods={"GET","POST"})
     * @IsGranted(InvitationVoter::ACCEPT, subject="invitation")
     * @Security("invitation.getToCompany() === company")
     *
     * @param Company $company
     * @param Request $request
     * @param Invitation $invitation
     * @param InvitationAcceptor $acceptor
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function accept(
        Company $company,
        Request $request,
        Invitation $invitation,
        InvitationAcceptor $acceptor
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(InvitationAcceptType::class, $invitation);
        $form->handleRequest($request);

        if (
            $user instanceof User
            && $form->isSubmitted()
            && $form->isValid()
        ) {
            $acceptor->accept($invitation, $user);

            return $this->redirectToRoute('company_dashboard', ['company' => $company->getId()]);
        }

        return $this->render(
            'invitation/accept.html.twig',
            [
                'company' => $company,
                'invitation' => $invitation,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/invitation/{id}", name="invitation_cancel", methods={"DELETE"})
     * @IsGranted(CompanyVoter::VIEW, subject="company")
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
