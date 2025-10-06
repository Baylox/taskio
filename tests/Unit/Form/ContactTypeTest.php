<?php

namespace App\Tests\Unit\Form;

use App\Dto\ContactData;
use App\Form\ContactType;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

#[CoversClass(ContactType::class)]
final class ContactTypeTest extends TypeTestCase
{
    protected function getExtensions(): array // We need this to test validation constraints
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message with sufficient length.',
            'website' => '', // Honeypot should be empty
        ];

        $contactData = new ContactData();
        $form = $this->factory->create(ContactType::class, $contactData);
        $form->submit($formData, false); // false = no validation

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals('John Doe', $contactData->getName());
        $this->assertEquals('john.doe@example.com', $contactData->getEmail());
        $this->assertEquals('Test Subject', $contactData->getSubject());
        $this->assertEquals('This is a test message with sufficient length.', $contactData->getMessage());
    }


    public function testFormHasRequiredFields(): void
    {
        $form = $this->factory->create(ContactType::class);

        $this->assertTrue($form->has('name'));
        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('subject'));
        $this->assertTrue($form->has('message'));
        $this->assertTrue($form->has('website')); // Honeypot
    }

    public function testEmailNormalization(): void
    {
        $formData = [
            'name' => 'John Doe',
            'email' => '  Test@Example.COM  ',
            'subject' => 'Test Subject',
            'message' => 'This is a test message with sufficient length.',
        ];

        $contactData = new ContactData();
        $form = $this->factory->create(ContactType::class, $contactData);
        $form->submit($formData, false); // false = no validation

        $this->assertEquals('test@example.com', $contactData->getEmail());
    }

}
