<?php

declare(strict_types=1);

namespace App\Controller\Company;

use App\Entity\Company;
use App\Repository\InvitationRepository;
use App\Repository\RightRepository;
use App\Security\CompanyVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
                'company' => $company,
                'rights' => $rightRepository->findByCompany($company),
                'invitations' => $invitationRepository->findByCompany($company),
            ]
        );
    }
}
