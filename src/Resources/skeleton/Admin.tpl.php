<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

final class <?= $class_name ?> extends AbstractAdmin
{

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
<?= $fields ?>;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
<?= $fields ?>->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
<?= $fields ?>;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
<?= $fields ?>;
    }
}
