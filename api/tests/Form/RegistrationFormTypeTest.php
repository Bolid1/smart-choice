<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\RegistrationFormType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormTypeTest extends TestCase
{
    /**
     * @covers \App\Form\RegistrationFormType::buildForm
     */
    public function testBuildForm(): void
    {
        $type = $this->createType();
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder
            ->expects($this->exactly(4))
            ->method('add')
            ->withConsecutive(
                [
                    $this->equalTo('email'),
                    $this->equalTo(EmailType::class),
                    $this->anything(),
                ],
                [
                    $this->equalTo('plainPassword'),
                    $this->equalTo(PasswordType::class),
                    $this->anything(),
                ],
                [
                    $this->equalTo('agreeTerms'),
                    $this->equalTo(CheckboxType::class),
                    $this->anything(),
                ],
                [
                    $this->equalTo('submit'),
                    $this->equalTo(SubmitType::class),
                    $this->anything(),
                ],
            )
            ->willReturnSelf()
        ;

        $type->buildForm($builder, []);
    }

    /**
     * @covers \App\Form\RegistrationFormType::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $type = $this->createType();
        $type->configureOptions($resolver = new OptionsResolver());
        $options = $resolver->resolve();
        $this->assertArrayHasKey('data_class', $options);
        $this->assertEquals(User::class, $options['data_class']);
    }

    private function createType(): RegistrationFormType
    {
        return new RegistrationFormType();
    }
}
