<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ImmutableArrayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['keys'] as $infos) {
            if ($infos instanceof FormBuilderInterface) {
                $builder->add($infos);
            } else {
                [$name, $type, $options] = $infos;

                if (\is_callable($options)) {
                    $extra = \array_slice($infos, 3);

                    $options = $options($builder, $name, $type, $extra);

                    if (null === $options) {
                        $options = [];
                    } elseif (!\is_array($options)) {
                        throw new \RuntimeException('the closure must return null or an array');
                    }
                }

                $builder->add($name, $type, $options);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'keys' => [],
        ]);

        $resolver->setAllowedValues('keys', static function (iterable $value): bool {
            foreach ($value as $subValue) {
                if (!$subValue instanceof FormBuilderInterface && (!\is_array($subValue) || 3 !== \count($subValue))) {
                    return false;
                }
            }

            return true;
        });
    }

    public function getBlockPrefix(): string
    {
        return 'sensiolabs_type_immutable_array';
    }
}
