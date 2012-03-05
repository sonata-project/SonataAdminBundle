<?php
/**
 * Created by JetBrains PhpStorm.
 * User: firesnake
 * Date: 22.02.12
 * Time: 17:14
 */

namespace Sonata\AdminBundle\Form\Type;
//namespace Mtools\ProjectBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

class DatepickerType extends AbstractType
{

    protected $dateFormat;
    protected $locale;

    public function __construct(TranslatorInterface $translator, $dateFormat, $locale)
    {
        $this->dateFormat = $dateFormat;
        $this->locale = $locale;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {

        $builder->resetClientTransformers();
        $builder->appendClientTransformer(new \Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer(null, null, $this->dateFormat));

    }

    public function getDefaultOptions(array $options)
    {
        $options = parent::getDefaultOptions($options);
        return $options;
    }

    public function getParent(array $options)
    {
        return 'number';
    }

    public function getName()
    {
        return 'datepicker';
    }

    public function buildView(FormView $view, FormInterface $form)
    {
        $view   ->setAttribute('class', 'sonata-datepicker')
                ->set('locale', $this->locale)
        ;
    }
}
