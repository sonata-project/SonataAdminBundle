<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Filter\ORM;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Translation\TranslatorInterface;

class BooleanFilter extends Filter
{
    protected $translator;

    /**
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $field
     * @param mixed $value
     * @return
     */
    public function filter($queryBuilder, $alias, $field, $value)
    {
        if ($this->getField()->getAttribute('multiple')) {

            $values = array();
            foreach ($value as $v) {
                if ($v == 'all') {
                    return;
                }

                $values[] = $v == 'true' ? 1 : 0;
            }

            if (count($values) == 0) {
                return;
            }

            $queryBuilder->andWhere($queryBuilder->expr()->in(sprintf('%s.%s',
                $alias,
                $field
            ), $values));

        } else {

            if ($value === null || $value == 'all') {
                return;
            }

            $queryBuilder->andWhere(sprintf('%s.%s = :%s',
                $alias,
                $field,
                $this->getName()
            ));

            $queryBuilder->setParameter($this->getName(), $value == 'true' ? 1 : 0);
        }
    }

    /**
     * @param \Symfony\Component\Form\FormFactory $formFactory
     * @return void
     */
    public function defineFieldBuilder(FormFactory $formFactory)
    {
        $options = array(
            'choices' => array(
                'all'   => $this->translator->trans('choice_all', array(), 'SonataAdminBundle'),
                'true'  => $this->translator->trans('choice_true', array(), 'SonataAdminBundle'),
                'false' => $this->translator->trans('choice_false', array(), 'SonataAdminBundle'),
            ),
            'required' => true
        );

        $options = array_merge($options, $this->getOption('filter_field_options', array()));

        $this->field = $formFactory->createNamedBuilder('choice', $this->getName(), null, $options)->getForm();
    }
}