<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\App\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

final class FormContractor implements FormContractorInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription): void
    {
    }

    public function getFormBuilder(string $name, array $formOptions = []): FormBuilderInterface
    {
        return $this->formFactory->createNamedBuilder($name, FormType::class, null, $formOptions);
    }

    public function getDefaultOptions(
        ?string $type,
        FieldDescriptionInterface $fieldDescription,
        array $formOptions = []
    ): array {
        return [];
    }
}
