<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Company;
use App\Entity\Right;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class RightVariable
{
    private RequestStack $requestStack;
    private Security $security;

    /**
     * RightVariable constructor.
     *
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(RequestStack $requestStack, Security $security)
    {
        $this->requestStack = $requestStack;
        $this->security = $security;
    }

    public function isAdmin(): ?bool
    {
        return ($right = $this->getRight()) && $right->isAdmin();
    }

    private function getRight(): ?Right
    {
        return ($user = $this->getUser()) && ($company = $this->getCompany())
            ? $company->getRightOf($user) : null;
    }

    private function getUser(): ?User
    {
        $user = null;
        if ($token = $this->security->getToken()) {
            $user = $token->getUser();
        }

        return $user instanceof User ? $user : null;
    }

    private function getCompany(): ?Company
    {
        $company = null;
        if ($request = $this->getRequest()) {
            $company = $request->get('company');
        }

        return $company instanceof Company ? $company : null;
    }

    private function getRequest(): ?Request
    {
        if (null !== $this->requestStack) {
            $request = $this->requestStack->getCurrentRequest();
        }

        return $request ?? null;
    }
}
