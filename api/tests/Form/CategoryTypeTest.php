<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Entity\Category;
use App\Form\CategoryType;
use Symfony\Component\Form\Test\TypeTestCase;

class CategoryTypeTest extends TypeTestCase
{
    /**
     * @covers \App\Form\CategoryType::buildForm
     * @covers \App\Form\CategoryType::configureOptions
     */
    public function testSubmitValidData(): void
    {
        $formData = [
            'name' => 'My awesome category',
        ];

        $model = new Category();
        // $formData will retrieve data from the form submission; pass it as the second argument
        $form = $this->factory->create(CategoryType::class, $model);

        $expected = new Category();
        // ...populate $object properties with the data stored in $formData
        $expected->name = $formData['name'];

        // submit the data to the form directly
        $form->submit($formData);

        // This check ensures there are no transformation failures
        $this->assertTrue($form->isSynchronized());

        // check that $formData was modified as expected when the form was submitted
        $this->assertEquals($expected, $model);
    }

    /**
     * @covers \App\Form\CategoryType::buildForm
     * @covers \App\Form\CategoryType::configureOptions
     */
    public function testCustomFormView(): void
    {
        $formData = new Category();
        // ... prepare the data as you need

        // The initial data may be used to compute custom view variables
        $view = $this->factory
            ->create(CategoryType::class, $formData)
            ->createView()
        ;

        $this->assertArrayHasKey('data', $view->vars);
        $this->assertInstanceOf(Category::class, $view->vars['data']);
    }
}
