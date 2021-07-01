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

namespace Sonata\AdminBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Model\LockInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 *
 * @phpstan-extends AbstractAdminExtension<object>
 */
final class LockExtension extends AbstractAdminExtension
{
    /**
     * @var string
     */
    private $fieldName = '_lock_version';

    public function configureFormFields(FormMapper $form): void
    {
        $admin = $form->getAdmin();
        $formBuilder = $form->getFormBuilder();

        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($admin): void {
            $data = $event->getData();
            $form = $event->getForm();

            if (!\is_object($data) || null !== $form->getParent()) {
                return;
            }

            $modelManager = $admin->getModelManager();

            if (!$modelManager instanceof LockInterface) {
                return;
            }

            if (null === $lockVersion = $modelManager->getLockVersion($data)) {
                return;
            }

            $form->add($this->fieldName, HiddenType::class, [
                'mapped' => false,
                'data' => $lockVersion,
            ]);
        });
    }

    public function preUpdate(AdminInterface $admin, object $object): void
    {
        if (!$admin->hasRequest()) {
            return;
        }

        $data = $admin->getRequest()->get($admin->getUniqId());
        if (!\is_array($data)) {
            return;
        }

        if (!isset($data[$this->fieldName])) {
            return;
        }

        $modelManager = $admin->getModelManager();

        if (!$modelManager instanceof LockInterface) {
            return;
        }

        $modelManager->lock($object, (int) $data[$this->fieldName]);
    }
}
