<?php

declare(strict_types=1);

namespace App\Controller\Company;

use App\DataPersister\AccountDataPersister;
use App\Entity\Account;
use App\Entity\Company;
use App\Form\AccountType;
use App\Security\AccountVoter;
use App\Security\CompanyVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/company/{company}/account")
 * @IsGranted("ROLE_USER")
 * @IsGranted(CompanyVoter::IS_ADMIN, subject="company")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/new", name="account_new", methods={"GET","POST"})
     *
     * @param Company $company
     * @param Request $request
     * @param AccountDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Company $company, Request $request, AccountDataPersister $persister): Response
    {
        $defaultCurrency = 'ru' === $request->getLocale() ? 'RUB' : 'USD';
        $account = (new Account())->setCompany($company)->setCurrency($defaultCurrency);

        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->isGranted(AccountVoter::CREATE, $account)) {
            $persister->persist($account);

            return $this->redirectToRoute('company_dashboard', ['company' => $company->getId()]);
        }

        return $this->render(
            'account/new.html.twig',
            [
                'company' => $company,
                'account' => $account,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="account_edit", methods={"GET","POST"})
     * @IsGranted(AccountVoter::EDIT, subject="account")
     * @Security("account.getCompany() === company")
     *
     * @param Company $company
     * @param Request $request
     * @param Account $account
     * @param AccountDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(
        Company $company,
        Request $request,
        Account $account,
        AccountDataPersister $persister
    ): Response {
        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $persister->persist($account);

            return $this->redirectToRoute('company_dashboard', ['company' => $company->getId()]);
        }

        return $this->render(
            'account/edit.html.twig',
            [
                'company' => $company,
                'account' => $account,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="account_delete", methods={"DELETE"})
     * @IsGranted(AccountVoter::DELETE, subject="account")
     * @Security("account.getCompany() === company")
     *
     * @param Company $company
     * @param Request $request
     * @param Account $account
     * @param AccountDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(
        Company $company,
        Request $request,
        Account $account,
        AccountDataPersister $persister
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$account->getId(), $request->request->get('_token'))) {
            $persister->remove($account);
        }

        return $this->redirectToRoute('company_dashboard', ['company' => $company->getId()]);
    }
}
