UPGRADE FROM 3.x to 4.0
=======================

## Removed `famfamfam` icon set

If you still need it, please set it up on your own!

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

## AdminHelper
The `AdminHelper::__construct` method changes its `Pool` param to a `PropertyAccessorInterface` one.

## BreadcrumbsBuilder
The `buildBreacrumbs` method may no longer be called from outside the class.

## BreadcrumbsBuilderInterface
The `buildBreacrumbs` method has been removed from the interface.

## SonataAdminExtension
The Twig filters that come with the bundle will no longer load a default template when used with a missing template.
The `sonata_admin` twig extension is now final. You may no longer extend it.

## SimplePager
Method `SimplePager::getResults` is always returning an array

## LockInterface
`LockInterface` extends from `ModelManagerInterface`.

## RouteCollectionInterface
`RouteCollection` implements `RouteCollectionInterface`.
