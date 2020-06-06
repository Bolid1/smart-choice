<?php

declare(strict_types=1);

namespace App\Controller\Company;

use App\Entity\Company;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/company/{company}")
 * @IsGranted("ROLE_USER")
 * @IsGranted("view", subject="company")
 */
class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="company_dashboard", methods={"GET"})
     *
     * @param \App\Entity\Company $company
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Company $company): Response
    {
        return $this->render(
            'dashboard/index.html.twig',
            [
                'company' => $company,
                'rights' => $company->getRights(),
            ]
        );
    }
}
