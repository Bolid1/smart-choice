<?php

namespace App\Controller\Company;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use App\DataPersister\InvitationDataPersister;
use App\Entity\Company;
use App\Entity\Invitation;
use App\Form\InvitationType;
use App\Repository\InvitationRepository;
use App\Security\InvitationExtension;
use App\Security\InvitationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/company/{company}/invitations")
 * @IsGranted("ROLE_USER")
 */
class InvitationController extends AbstractController
{
    /**
     * @Route("/", name="invitations_list", methods={"GET"})
     *
     * @param \App\Entity\Company $company
     * @param \App\Repository\InvitationRepository $repository
     * @param \App\Security\InvitationExtension $extension
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(Company $company, InvitationRepository $repository, InvitationExtension $extension): Response
    {
        $queryBuilder = $repository
            ->createQueryBuilder('invitation')
            ->where("invitation.toCompany = :company")
            ->setParameter('company', $company)
        ;

        $extension->applyToCollection(
            $queryBuilder,
            new QueryNameGenerator(),
            Invitation::class,
            'get'
        );

        return $this->render(
            'invitation/index.html.twig',
            [
                'invitations' => $queryBuilder->getQuery()->execute(),
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

            return $this->redirectToRoute('invitations_list');
        }

        return $this->render(
            'invitation/edit.html.twig',
            [
                'invitation' => $invitation,
                'form' => $form->createView(),
            ]
        );
    }
}
