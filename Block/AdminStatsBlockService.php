<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Block;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractAdminBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author Christian Gripp <mail@core23.de>
 */
class AdminStatsBlockService extends AbstractAdminBlockService
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * Colors available in AdminLTE 2.3.3 css for background with bg-xxx and bg-xxx-active.
     *
     * @var string[]
     */
    private $colors = array(
        'bg-red' => 'red',
        'bg-yellow' => 'yellow',
        'bg-aqua' => 'aqua',
        'bg-blue' => 'blue',
        'bg-light-blue' => 'light-blue',
        'bg-green' => 'green',
        'bg-navy' => 'navy',
        'bg-teal' => 'teal',
        'bg-olive' => 'olive',
        'bg-lime' => 'lime',
        'bg-orange' => 'orange',
        'bg-fuchsia' => 'fuchsia',
        'bg-purple' => 'purple',
        'bg-maroon' => 'maroon',
        'bg-black' => 'black',
    );

    /**
     * Each template corresponds to the AdminLTE 2.3.3 widgets.
     *
     * @var string[]
     */
    private $templates = array(
        'SonataAdminBundle:Block:block_stats_simple.html.twig' => 'simple',
        'SonataAdminBundle:Block:block_stats_link.html.twig' => 'link',
    );

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param string                   $name
     * @param EngineInterface          $templating
     * @param Pool                     $pool
     * @param TranslatorInterface|null $translator
     *
     * NEXT_MAJOR: make the translator required
     */
    public function __construct($name, EngineInterface $templating, Pool $pool, TranslatorInterface $translator = null)
    {
        parent::__construct($name, $templating);

        $this->pool = $pool;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $form, BlockInterface $block)
    {
        $admins = array();
        foreach ($this->pool->getAdminGroups() as $group => $data) {
            $groupLabel = $this->translator ?
                $this->translator->trans($data['label'], array(), $data['label_catalogue']) :
                $data['label'];

            foreach ($data['items'] as $item) {
                $label = $item['label'] && $this->translator ? $this->translator->trans($item['label'], array(), $data['label_catalogue']) : $item['admin'];

                $admins[$groupLabel][$label] = $item['admin'];
            }
        }

        $colorChoices = $this->colors;
        $colorChoiceOptions = array(
            'required' => true,
            'label' => 'form.label_color',
            'choice_translation_domain' => false,
        );

        $templateChoices = $this->templates;
        $templateChoiceOptions = array(
            'required' => true,
            'label' => 'form.label_template',
            'choice_label' => function ($value, $key, $index) {
                return 'form.template_'.$index;
            },
        );

        // NEXT_MAJOR: remove SF 2.7+ BC
        if (method_exists('Symfony\Component\Form\AbstractType', 'configureOptions')) {
            // choice_as_value options is not needed in SF 3.0+
            if (method_exists('Symfony\Component\Form\FormTypeInterface', 'setDefaultOptions')) {
                $colorChoiceOptions['choices_as_values'] = true;
            }
            $colorChoices = array_flip($colorChoices);
            $templateChoices = array_flip($templateChoices);
        }
        $colorChoiceOptions['choices'] = $colorChoices;
        $templateChoiceOptions['choices'] = $templateChoices;

        // NEXT_MAJOR: Remove this line when drop Symfony <2.8 support
        $arrayType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Sonata\CoreBundle\Form\Type\ImmutableArrayType' : 'sonata_type_immutable_array';
        $choiceType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Symfony\Component\Form\Extension\Core\Type\ChoiceType' : 'choice';
        $textType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Symfony\Component\Form\Extension\Core\Type\TextType' : 'text';
        $yamlType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Sonata\AdminBundle\Form\Type\YamlType' : 'sonata_type_yaml';
        $numberType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Symfony\Component\Form\Extension\Core\Type\NumberType' : 'number';

        $form->add('settings', $arrayType, array(
            'keys' => array(
                array('code', $choiceType, array(
                   'required' => true,
                   'label' => 'form.label_code',
                   'choices' => $admins,
                   'choice_translation_domain' => false,
                )),
                array('filters', $yamlType, array(
                    'required' => false,
                    'label' => 'form.label_filters',
                )),
                array('text', $textType, array(
                    'required' => false,
                    'label' => 'form.label_text',
                )),
                array('class', $textType, array(
                    'required' => false,
                    'label' => 'form.label_class',
                )),
                array('icon', $textType, array(
                    'required' => false,
                    'label' => 'form.label_icon',
                )),
                array('limit', $numberType, array(
                    'required' => false,
                    'label' => 'form.label_limit',
                )),
                array('color', $choiceType, $colorChoiceOptions),
                array('template', $choiceType, $templateChoiceOptions),
            ),
            'translation_domain' => 'SonataAdminBundle',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $admin = $this->pool->getAdminByAdminCode($blockContext->getSetting('code'));

        $datagrid = $admin->getDatagrid();

        $filters = $blockContext->getSetting('filters');

        if (!isset($filters['_per_page'])) {
            $filters['_per_page'] = array('value' => $blockContext->getSetting('limit'));
        }

        foreach ($filters as $name => $data) {
            $datagrid->setValue($name, isset($data['type']) ? $data['type'] : null, $data['value']);
        }

        $datagrid->buildPager();

        return $this->renderPrivateResponse($blockContext->getTemplate(), array(
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'admin_pool' => $this->pool,
            'admin' => $admin,
            'pager' => $datagrid->getPager(),
            'datagrid' => $datagrid,
        ), $response);
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        if (($code = $block->getSetting('code')) && $code !== '' && !$this->pool->getAdminByAdminCode($code)) {
            // If we specified a admin, check that it exists
            $errorElement->with('code')
                ->addViolation('sonata.admin.not_existing', array('code' => $code))
            ->end();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'icon' => 'fa fa-line-chart',
            'text' => 'Statistics',
            'color' => 'bg-aqua',
            'code' => false,
            'class' => '',
            'filters' => array(),
            'limit' => 1000,
            'template' => 'SonataAdminBundle:Block:block_stats_simple.html.twig',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (!is_null($code) ? $code : $this->getName()), false, 'SonataAdminBundle', array(
            'class' => 'fa fa-dashboard',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getJavascripts($media)
    {
        return array(
            'bundles/sonataadmin/stats-block.js',
        );
    }

    /**
     * Set possible widget colors.
     *
     * @param string[] $colors
     */
    public function setColors(array $colors)
    {
        $this->colors = $colors;
    }

    /**
     * Set possible templates.
     *
     * @param string[] $templates
     */
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
    }
}
