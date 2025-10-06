<?php

namespace App\Tests\Unit\Form;

use App\Entity\Account;
use App\Form\ProfileType;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

#[CoversClass(ProfileType::class)]
final class ProfileTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testFormHasRequiredFields(): void
    {
        $form = $this->factory->create(ProfileType::class);

        $this->assertTrue($form->has('name'));
        $this->assertTrue($form->has('lastname'));
        $this->assertTrue($form->has('plainPassword'));
    }

    public function testPasswordIsOptional(): void
    {
        $form = $this->factory->create(ProfileType::class);

        $this->assertFalse($form->get('plainPassword')->isRequired());
    }
}
