<?php

declare(strict_types=1);

namespace App\Controller\API\V0;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthJsonController extends AbstractController
{
    /**
     * @Route("/api/v0/auth/json", name="api_v0_auth_json", methods={"POST"})
     */
    public function login(): Response
    {
        $user = $this->getUser();
        $response = [];
        if ($user instanceof User) {
            $response = [
                'email' => $user->getEmail(),
            ];
        }

        return $this->json($response, $response ? 200 : 401);
    }
}
