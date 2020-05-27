<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\LoginFormAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class LoginFormAuthenticatorTest extends TestCase
{
    private UrlGeneratorInterface $urlGenerator;
    private CsrfTokenManagerInterface $csrfTokenManager;
    private UserPasswordEncoderInterface $passwordEncoder;
    private LoginFormAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->csrfTokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $this->passwordEncoder = $this->createMock(UserPasswordEncoderInterface::class);

        $this->authenticator = new LoginFormAuthenticator(
            $this->urlGenerator,
            $this->csrfTokenManager,
            $this->passwordEncoder,
        );
    }

    /**
     * @covers \App\Security\LoginFormAuthenticator::supports
     */
    public function testSupports(): void
    {
        $request = new Request(
            [],
            [],
            ['_route' => LoginFormAuthenticator::LOGIN_ROUTE],
            [],
            [],
            ['REQUEST_METHOD' => 'POST'],
        );

        $this->assertTrue($this->authenticator->supports($request));

        $request = new Request(
            [],
            [],
            ['_route' => 'home'],
            [],
            [],
            ['REQUEST_METHOD' => 'POST'],
        );

        $this->assertFalse($this->authenticator->supports($request));

        $request = new Request(
            [],
            [],
            ['_route' => LoginFormAuthenticator::LOGIN_ROUTE],
            [],
            [],
            ['REQUEST_METHOD' => 'GET'],
        );

        $this->assertFalse($this->authenticator->supports($request));
    }

    /**
     * @covers \App\Security\LoginFormAuthenticator::getCredentials
     */
    public function testGetCredentials(): void
    {
        $request = new Request(
            [],
            [
                'email' => $email = 'foo@bar.bax',
                'password' => 'my_password',
                'csrf_token' => 'my_token',
            ],
        );

        $request->setSession($session = $this->createMock(SessionInterface::class));
        $session
            ->expects($this->once())
            ->method('set')
            ->with(Security::LAST_USERNAME, $email)
        ;

        $credentials = [
            'email' => $email,
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        $this->assertEquals($credentials, $this->authenticator->getCredentials($request));
    }

    /**
     * @covers \App\Security\LoginFormAuthenticator::getUser
     */
    public function testGetUser(): void
    {
        $credentials = [
            'email' => 'foo@bar.bax',
            'password' => 'my_password',
            'csrf_token' => 'my_token',
        ];

        $userProvider = $this->createMock(UserProviderInterface::class);

        $this->csrfTokenManager
            ->expects($this->once())
            ->method('isTokenValid')
            ->willReturn(true)
        ;

        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($credentials['email'])
            ->willReturn($user = new User())
        ;

        $this->assertSame($user, $this->authenticator->getUser($credentials, $userProvider));
    }

    /**
     * @covers \App\Security\LoginFormAuthenticator::checkCredentials
     */
    public function testCheckCredentials(): void
    {
        $credentials = [
            'email' => 'foo@bar.bax',
            'password' => 'my_password',
            'csrf_token' => 'my_token',
        ];
        $user = new User();

        $this->passwordEncoder
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, $credentials['password'])
            ->willReturn(true)
        ;

        $this->assertTrue($this->authenticator->checkCredentials($credentials, $user));
    }

    /**
     * @covers \App\Security\LoginFormAuthenticator::onAuthenticationSuccess
     */
    public function testOnAuthenticationSuccess(): void
    {
        $request = new Request();
        $request->setSession($session = $this->createMock(SessionInterface::class));

        $token = $this->createMock(TokenInterface::class);
        $providerKey = 'foo';

        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(LoginFormAuthenticator::REDIRECT_ROUTE)
            ->willReturn($redirectTo = '/baz')
        ;

        $response = $this->authenticator->onAuthenticationSuccess($request, $token, $providerKey);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals($redirectTo, $response->getTargetUrl());
    }
}
