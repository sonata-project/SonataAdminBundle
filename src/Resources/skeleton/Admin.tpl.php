<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

final class <?= $class_name ?> extends AbstractAdmin
{

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
<?= $fields ?>;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
<?= $fields ?>->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
<?= $fields ?>;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
<?= $fields ?>;
    }
}
