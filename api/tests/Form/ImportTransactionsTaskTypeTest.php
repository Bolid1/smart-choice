<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Entity\ImportTransactionsTask;
use App\Form\ImportTransactionsTaskType;
use Symfony\Component\Form\Test\TypeTestCase;

class ImportTransactionsTaskTypeTest extends TypeTestCase
{
    /**
     * @covers \App\Form\ImportTransactionsTaskType::buildForm
     * @covers \App\Form\ImportTransactionsTaskType::configureOptions
     */
    public function testSubmitValidData(): void
    {
        $formData = [
            'scheduledTime' => '2020-06-28 16:18:45',
            'mimeType' => 'json',
            'data' => '{"foo":"bar"}',
        ];

        $model = new ImportTransactionsTask();
        // $formData will retrieve data from the form submission; pass it as the second argument
        $form = $this->factory->create(ImportTransactionsTaskType::class, $model);

        $expected = new ImportTransactionsTask();
        // ...populate $object properties with the data stored in $formData
        $expected->mimeType = $formData['mimeType'];
        $expected->data = $formData['data'];

        // submit the data to the form directly
        $form->submit($formData);

        // This check ensures there are no transformation failures
        $this->assertTrue($form->isSynchronized());

        // check that $formData was modified as expected when the form was submitted
        $this->assertEquals($expected, $model);
    }

    /**
     * @covers \App\Form\ImportTransactionsTaskType::buildForm
     * @covers \App\Form\ImportTransactionsTaskType::configureOptions
     */
    public function testCustomFormView(): void
    {
        $formData = new ImportTransactionsTask();
        // ... prepare the data as you need

        // The initial data may be used to compute custom view variables
        $view = $this->factory
            ->create(ImportTransactionsTaskType::class, $formData)
            ->createView()
        ;

        $this->assertArrayHasKey('data', $view->vars);
        $this->assertInstanceOf(ImportTransactionsTask::class, $view->vars['data']);
    }
}
