<?php

declare(strict_types=1);

namespace App\Controller\Company;

use App\DataPersister\InvitationDataPersister;
use App\Entity\Company;
use App\Entity\Invitation;
use App\Entity\User;
use App\Form\InvitationType;
use App\Repository\InvitationRepository;
use App\Repository\RightRepository;
use App\Security\CompanyVoter;
use App\Security\InvitationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/company/{company}/users")
 * @IsGranted("ROLE_USER")
 */
class UsersController extends AbstractController
{
    /**
     * @Route("/", name="company_users_index", methods={"GET"})
     * @IsGranted(CompanyVoter::IS_ADMIN, subject="company")
     *
     * @param Company $company
     * @param RightRepository $rightRepository
     * @param InvitationRepository $invitationRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(
        Company $company,
        RightRepository $rightRepository,
        InvitationRepository $invitationRepository
    ): Response {
        return $this->render(
            'users/index.html.twig',
            [
                'rights' => $rightRepository->findByCompany($company),
                'invitations' => $invitationRepository->findByCompany($company),
            ]
        );
    }

    /**
     * @Route("/invite", name="invite_user_to_company", methods={"GET","POST"})
     *
     * @param \App\Entity\Company $company
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\DataPersister\InvitationDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function invite(Company $company, Request $request, InvitationDataPersister $persister): Response
    {
        $invitation = new Invitation();
        $user = $this->getUser();
        if ($user instanceof User) {
            $invitation->setFromUser($user);
        }
        $invitation->setToCompany($company);

        $form = $this->createForm(InvitationType::class, $invitation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->isGranted(InvitationVoter::CREATE, $invitation)) {
            $persister->persist($invitation);

            return $this->redirectToRoute('invitation_edit', ['id' => $invitation->getId(), 'company' => $company->getId()]);
        }

        return $this->render(
            'invitation/new.html.twig',
            [
                'invitation' => $invitation,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/invitation/{id}", name="company_invitation_cancel", methods={"DELETE"})
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
    public function cancelInvitation(
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
