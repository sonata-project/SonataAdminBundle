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

namespace Sonata\AdminBundle\Block;

use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Validator\ErrorElement;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
abstract class AbstractAdminBlockService extends AbstractBlockService implements EditableBlockService
{
    /**
     * {@inheritdoc}
     */
    public function configureEditForm(FormMapper $formMapper, BlockInterface $block): void
    {
        $this->configureCreateForm($formMapper, $block);
    }

    /**
     * {@inheritdoc}
     */
    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
    }

    /**
     * {@inheritdoc}
     *
     * @param string|null $code This param must be removed
     */
    public function getMetadata($code = null): MetadataInterface
    {
        return new Metadata($this->getName(), $this->getName(), false, 'SonataAdminBundle', ['class' => 'fa fa-file']);
    }
}
