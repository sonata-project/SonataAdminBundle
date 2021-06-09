UPGRADE FROM 3.x to 4.0
=======================

## Upgrade to Font Awesome 5

Some icons have been renamed by Font Awesome, and the correct class is now `fas` or `fab` instead of
`fa`, for example before you would have used `fa fa-edit`, and after `fas fa-edit`.

Sonata includes the compatibility layer of Font Awesome 5 that ensure the old icons names from version 4
still works. So we encourage to upgrade the names of all your custom icons, but the old code should
still work.

## Removed `famfamfam` icon set

If you still need it, please set it up on your own!

## Migration to NPM

Frontend dependencies are handled with NPM. Bower is not used anymore.

A lot of assets that were previously public are handled with NPM and placed in a private `node_modules/` directory.
From these dependencies, only the necessary files are exposed publicly through Webpack Encore.

The jQuery UI dependency was fully included before, but now we only include the sortable widget (JavaScript and CSS), the rest of this dependency is not exposed. If you are adding more JavaScript or CSS using another widget of jQuery UI please include it yourself.

Please check the `src/Resources/public` and the documentation to see the used CSS, JavaScript, images and fonts.

If you are customising (specially removing standard JavaScript or CSS) assets, this will affect you.

## Deprecations

All the deprecated code introduced on 3.x is removed on 4.0.

Please read [3.x](https://github.com/sonata-project/SonataAdminBundle/tree/3.x) upgrade guides for more information.

See also the [diff code](https://github.com/sonata-project/SonataAdminBundle/compare/3.x...4.0.0).

## Final classes

Some classes and methods are now `final` and should not be overridden:

* `Sonata\AdminBundle\Admin\AbstractAdmin::getActionButtons`
* `Sonata\AdminBundle\Admin\AbstractAdmin::getBatchActions`
* `Sonata\AdminBundle\Admin\AbstractAdmin::urlize`
* `Sonata\AdminBundle\Admin\Extension\LockExtension`
* `Sonata\AdminBundle\Admin\FieldDescriptionCollection`
* `Sonata\AdminBundle\Block\AdminListBlockService`
* `Sonata\AdminBundle\Block\AdminSearchBlockService`
* `Sonata\AdminBundle\Block\AdminStatsBlockService`
* `Sonata\AdminBundle\Command\ExplainAdminCommand`
* `Sonata\AdminBundle\Command\GenerateObjectAclCommand`
* `Sonata\AdminBundle\Command\ListAdminCommand`
* `Sonata\AdminBundle\Command\SetupAclCommand`
* `Sonata\AdminBundle\Command\Validators`
* `Sonata\AdminBundle\Datagrid\Datagrid`
* `Sonata\AdminBundle\Datagrid\SimplePager`
* `Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass`
* `Sonata\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass`
* `Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass`
* `Sonata\AdminBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass`
* `Sonata\AdminBundle\DependencyInjection\Configuration`
* `Sonata\AdminBundle\DependencyInjection\SonataAdminExtension`
* `Sonata\AdminBundle\DependencyInjection\Compiler\ModelManagerCompilerPass`
* `Sonata\AdminBundle\Event\AdminEventExtension`
* `Sonata\AdminBundle\Event\ConfigureEvent`
* `Sonata\AdminBundle\Event\ConfigureMenuEvent`
* `Sonata\AdminBundle\Event\ConfigureQueryEvent`
* `Sonata\AdminBundle\Event\PersistenceEvent`
* `Sonata\AdminBundle\Exception\LockException`
* `Sonata\AdminBundle\Exception\ModelManagerException`
* `Sonata\AdminBundle\Exception\NoValueException`
* `Sonata\AdminBundle\Filter\FilterFactory`
* `Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader`
* `Sonata\AdminBundle\Form\DataTransformer\ArrayToModelTransformer`
* `Sonata\AdminBundle\Form\DataTransformer\ModelToIdPropertyTransformer`
* `Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer`
* `Sonata\AdminBundle\Form\DataTransformer\ModelsToArrayTransformer`
* `Sonata\AdminBundle\Form\EventListener\MergeCollectionListener`
* `Sonata\AdminBundle\Form\Extension\ChoiceTypeExtension`
* `Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension`
* `Sonata\AdminBundle\Form\Extension\Field\Type\MopaCompatibilityTypeFieldExtension`
* `Sonata\AdminBundle\Form\Type\AclMatrixType`
* `Sonata\AdminBundle\Form\Type\AdminType`
* `Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType`
* `Sonata\AdminBundle\Form\Type\CollectionType`
* `Sonata\AdminBundle\Form\Type\Filter\ChoiceType`
* `Sonata\AdminBundle\Form\Type\Filter\DateRangeType`
* `Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType`
* `Sonata\AdminBundle\Form\Type\Filter\DateTimeType`
* `Sonata\AdminBundle\Form\Type\Filter\DateType`
* `Sonata\AdminBundle\Form\Type\Filter\DefaultType`
* `Sonata\AdminBundle\Form\Type\Filter\NumberType`
* `Sonata\AdminBundle\Form\Type\ModelAutocompleteType`
* `Sonata\AdminBundle\Form\Type\ModelHiddenType`
* `Sonata\AdminBundle\Form\Type\ModelListType`
* `Sonata\AdminBundle\Form\Type\ModelReferenceType`
* `Sonata\AdminBundle\Form\Type\ModelType`
* `Sonata\AdminBundle\Guesser\TypeGuesserChain`
* `Sonata\AdminBundle\Manipulator\ServicesManipulator`
* `Sonata\AdminBundle\Menu\MenuBuilder`
* `Sonata\AdminBundle\Model\AuditManager`
* `Sonata\AdminBundle\Route\AdminPoolLoader`
* `Sonata\AdminBundle\Route\DefaultRouteGenerator`
* `Sonata\AdminBundle\Route\RouteCollection`
* `Sonata\AdminBundle\Route\RoutesCache`
* `Sonata\AdminBundle\Route\RoutesCacheWarmUp`
* `Sonata\AdminBundle\Security\Acl\AdminPermissionMap`
* `Sonata\AdminBundle\Security\Acl\Permission\MaskBuilder`
* `Sonata\AdminBundle\Security\Handler\AclSecurityHandler`
* `Sonata\AdminBundle\Security\Handler\NoopSecurityHandler`
* `Sonata\AdminBundle\SonataAdminBundle`
* `Sonata\AdminBundle\Translator\BCLabelTranslatorStrategy`
* `Sonata\AdminBundle\Translator\FormLabelTranslatorStrategy`
* `Sonata\AdminBundle\Translator\NativeLabelTranslatorStrategy`
* `Sonata\AdminBundle\Translator\UnderscoreLabelTranslatorStrategy`
* `Sonata\AdminBundle\Twig\Extension\SonataAdminExtension`
* `Sonata\AdminBundle\Twig\GlobalVariables`
* `Sonata\AdminBundle\Util\AdminAclManipulator`
* `Sonata\AdminBundle\Util\FormBuilderIterator`
* `Sonata\AdminBundle\Util\FormViewIterator`
* `Sonata\AdminBundle\Util\AdminAclManipulator`

## Admin
If you have implemented a custom admin, you must adapt the signature of the following new methods to match the one in `AdminInterface` again:
 * `hasAccess`
 * `configureActionButtons`
 * `getExportFields`
 * `setTemplates`
 * `setTemplate`
 * `getTemplates`
 * `getClassnameLabel`
 * `getPersistentParameter`
 * `preValidate`
 * `getSubClasses`
 * `addSubClass`
 * `getDashboardActions`
 * `getActionButtons`
 * `isCurrentRoute`

The following methods changed their visiblity to protected:
 * `configureActionButtons`
 * `configure`
 * `urlize`

The method signature of `configureActionButtons` has changed. A new parameter `buttonList` was added.

## AdminExtension
If you have implemented a custom admin extension, you must adapt the signature of the following new methods to match the one in `AdminExtensionInterface` again:
 * `configureActionButtons`
 * `configureBatchActions`
 * `getAccessMapping`

## AdminListBlockService
The third argument of the `AdminListBlockService::__construct` method is now mandatory.

## AdminSearchBlockService
The fourth argument of the `AdminListBlockService::__construct` method is now mandatory.

## AdminHelper
The `AdminHelper::__construct` method changes its `Pool` param to a `PropertyAccessorInterface` one.

## BreadcrumbsBuilder
The `buildBreadcrumbs` method may no longer be called from outside the class.

## BreadcrumbsBuilderInterface
The `buildBreadcrumbs` method has been removed from the interface.

## SonataAdminExtension
The Twig filters that come with the bundle will no longer load a default template when used with a missing template.
The `sonata_admin` twig extension is now final. You may no longer extend it.

## SimplePager
Method `SimplePager::getResults` is always returning an array

## LockInterface
`LockInterface` extends from `ModelManagerInterface`.

## RouteCollectionInterface
`RouteCollection` implements `RouteCollectionInterface`.

## SearchHandler
When there is no searchable filters, `SearchHandler::search()` returns `null`. Previously, it was returning `false`.

## Sonata\AdminBundle\Controller\CRUDController
When the service `security.csrf.token_manager` is not available, `getCsrfToken()` returns `null`. Previously, it was returning `false`.

The `isXmlHttpRequest()`, `redirectTo()`, `isPreviewApproved()`, `isInPreviewMode()`, `isPreviewDeclined()`,
`validateCsrfToken()` signatures was changed. They now require to pass the request as first argument.
The `CRUDController::getRequest()` method was removed.

## FilterInterface

The type for argument 4 in `apply()` method has been changed from `array` to `Sonata\AdminBundle\Filter\Model\FilterData`.

Before:
```php
public function apply(ProxyQueryInterface $query, array $filterData): void;
```
After:
```php
public function apply(ProxyQueryInterface $query, FilterData $filterData): void;
```

## FormMapper labels
The form label are now correctly using the label translator strategy for field with `.`
(which won't be replaced by `__`). For instance, with the underscore label strategy, the
label `foo.barBaz` was previously `form.label_foo__bar_baz` and now is `form.label_foo_bar_baz`
to be consistent with others labels like `show.label_foo_bar_baz`.

## MutableTemplateRegistry::setTemplates and AbstractAdmin::setTemplates
They don't reset the existing templates anymore.

## BaseFieldDescription, FieldDescriptionCollection, FieldDescriptionInterface and FieldDescriptionRegistryInterface
Moved from the `Sonata\AdminBundle\Admin` to the `Sonata\AdminBundle\FieldDescription` namespace.

## BuilderInterface

Remove `AdminInterface $admin` argument from
- `BuilderInterface::fixFieldDescription()`
- `DatagridBuilderInterface::addFilter()`
- `ListBuilderInterface::buildField()`
- `ListBuilderInterface::addField()`
- `ShowBuilderInterface::addField()`

Use `$fieldDescription->getAdmin()` to access to the admin value.

## ListMapper constants

`ListMapper::NAME_ACTIONS` change to `_actions`.
`ListMapper::NAME_BATCH` change to `_batch`.
`ListMapper::NAME_SELECT` change to `_select`.

Be aware it implies that the following code
```php
protected function configureListFields(ListMapper $listMapper)
{
    $listMapper
        ->add('_action', null, [
            'actions' => [
                'show' => [],
                'edit' => [],
                'delete' => [],
            ]
        ]);
}
```
should be updated to
```php
protected function configureListFields(ListMapper $listMapper)
{
    $listMapper
        ->add('_actions', null, [
            'actions' => [
                'show' => [],
                'edit' => [],
                'delete' => [],
            ]
        ]);
}
```
but the best is to use the constant `ListMapper::NAME_ACTIONS`.
