UPGRADE 3.x
===========

UPGRADE FROM 3.x to 3.x
=======================

### `Sonata\AdminBundle\Controller\CRUDController::historyCompareRevisionsAction()`

- Deprecated route parameter "base_revision" in favor of "baseRevision";
- Deprecated route parameter "compare_revision" in favor of "compareRevision".

Before:
```php
$admin->generateObjectUrl('history_compare_revisions', $subject, [
    'base_revision' => $currentRev,
    'compare_revision' => $rev,
]);
```

After:
```php
$admin->generateObjectUrl('history_compare_revisions', $subject, [
    'baseRevision' => $currentRev,
    'compareRevision' => $rev,
]);
```

UPGRADE FROM 3.89 to 3.90
=========================

### Deprecated `Sonata\AdminBundle\Guesser\TypeGuesserInterface` interface.

Use `Sonata\AdminBundle\FieldDescription\TypeGuesserInterface` interface instead.

### Deprecated `Sonata\AdminBundle\Guesser\TypeGuesserChain` class.

Use `Sonata\AdminBundle\FieldDescription\TypeGuesserChain` class instead.

### Deprecated `Sonata\AdminBundle\Model\ModelManagerInterface::getModelInstance()` method.

Use `Sonata\AdminBundle\Admin\AbstractAdmin::createNewInstance()` method instead.

UPGRADE FROM 3.88 to 3.89
=========================

### Deprecated `Sonata\AdminBundle\Model\ModelManager::getNewFieldDescriptionInstance()` method.

This method has been deprecated in favor of `FieldFactoryInterface::create()`.

### Deprecated overriding `AbstractAdmin::getNewInstance()`.

Use `AbstractAdmin::alterNewInstance()` instead.

### Deprecated passing the field type and options to `DatagridMapper::add` as parameters 4 and 5.

Before:
```php
use Sonata\AdminBundle\Admin\AbstractAdmin;

final class MyAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('foo', null, [], MyFormType::class, ['foo' => 'bar']);
    }
}
```
After
```php
use Sonata\AdminBundle\Admin\AbstractAdmin;

final class MyAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('foo', null, [
            'field_type' => MyFormType::class,
            'field_options' => ['foo' => 'bar'],
        ]);
    }
}
```

### Deprecated the `Sonata\AdminBundle\AdminFieldDescription` `'code'` option.

Use the `accessor` option instead.

### Deprecated `Sonata\AdminBundle\Admin\AbstractAdmin::formOptions` property.

This property has been replaced by the new method `Sonata\AdminBundle\Admin\AbstractAdmin::configureFormOptions()`

Before:
```php
use Sonata\AdminBundle\Admin\AbstractAdmin;

final class MyAdmin extends AbstractAdmin
{
    protected $formOptions = [
        'validation_groups' => ['Default', 'MyAdmin'],
    ];
}
```

After:
```php
use Sonata\AdminBundle\Admin\AbstractAdmin;

final class MyAdmin extends AbstractAdmin
{
    protected function configureFormOptions(array &$formOptions): void
    {
        $formOptions['validation_groups'] = ['Default', 'MyAdmin'];
    }
}
```

### Deprecated `Sonata\AdminBundle\Admin\Pool::setTemplateRegistry()` method.

This method has been deprecated without replacement.

UPGRADE FROM 3.86 to 3.87
=========================

### Deprecated `DateOperatorType::TYPE_NULL` and `DateOperatorType::TYPE_NOT_NULL`

We recommend using a specific filter for null values instead.

### Sonata\AdminBundle\Twig\Extension\SonataAdminExtension

- Deprecated `SonataAdminExtension::MOMENT_UNSUPPORTED_LOCALES` constant.
- Deprecated `SonataAdminExtension::setXEditableTypeMapping()` method.
- Deprecated `SonataAdminExtension::getXEditableType()` method.
- Deprecated `SonataAdminExtension::getXEditableChoices()` method.
- Deprecated `SonataAdminExtension::getCanonicalizedLocaleForMoment()` method in favor of
  `CanonicalizerExtension::getCanonicalizedLocaleForMoment()`.
- Deprecated `SonataAdminExtension::getCanonicalizedLocaleForSelect2()` method in favor of
  `CanonicalizerExtension::getCanonicalizedLocaleForSelect2()`.
- Deprecated `SonataAdminExtension::isGrantedAffirmative()` method in favor of
  `SecurityExtension::isGrantedAffirmative()`.
- Deprecated `SonataAdminExtension::renderListElement()` method in favor of
  `RenderElementExtension::renderListElement()`.
- Deprecated `SonataAdminExtension::renderViewElement()` method in favor of
  `RenderElementExtension::renderViewElement()`.
- Deprecated `SonataAdminExtension::renderViewElementCompare()` method in favor of
  `RenderElementExtension::renderViewElementCompare()`.
- Deprecated `SonataAdminExtension::renderRelationElement()` method in favor of
  `RenderElementExtension::renderRelationElement()`.
- Deprecated `SonataAdminExtension::getTemplate()` method.
- Deprecated `SonataAdminExtension::getTemplateRegistry()` method.

### Sonata\AdminBundle\Datagrid\PagerInterface

Deprecated `getResults()` method in favor of `getCurrentPageResults()`.

UPGRADE FROM 3.85 to 3.86
=========================

### Sonata\AdminBundle\Datagrid\PagerInterface

Deprecated `getNbResults()` method in favor of `countResults()`.

### Sonata\AdminBundle\Datagrid\Pager and Sonata\AdminBundle\Datagrid\SimplePager

Deprecated `$nbResults` property, `getNbResults()` and `setNbResults()` methods.

### Deprecated `Sonata\AdminBundle\Templating\TemplateRegistryInterface::TYPE_*` constants.

They have been moved to `Sonata\AdminBundle\Admin\FieldDescriptionInterface`.

### Sonata\AdminBundle\Controller\CRUDController

Deprecated `configure()` method for configuring the associated admin, you MUST call `configureAdmin()` method instead.

### Sonata\AdminBundle\Admin\Pool

- `Sonata\AdminBundle\Admin\Pool::setAdminServiceIds()` method has been deprecated. You MUST pass service ids as
  argument 2 to the constructor.
- `Sonata\AdminBundle\Admin\Pool::setAdminGroups()` method has been deprecated. You MUST pass admin groups as
  argument 3 to the constructor.
- `Sonata\AdminBundle\Admin\Pool::setAdminClasses()` method has been deprecated. You MUST pass admin classes as
  argument 4 to the constructor.

UPGRADE FROM 3.83 to 3.84
=========================

### Deprecated `FieldDescriptionInterface::getFieldValue()`

`BaseFieldDescription::getFieldValue()` will become protected.

### `RouteCollection` now implements `RouteCollectionInterface`

In 4.0, `AbstractAdmin::configureRoutes` and `AdminExtensionInterface::configureRoutes` will receive a
`RouteCollectionInterface` instance instead of a `RouteCollection` instance, you can update your code before ugprading
to 4.0.

Before:
```php
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

final class MyAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollection $collection): void
    {
        $collection->add('my_route');
    }
}
```

After:
```php
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

final class MyAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('my_route');
    }
}
```
This only will work with PHP >= 7.4, where fully support to contravariance was added.

### Sonata\AdminBundle\Admin\BaseFieldDescription

Method `__construct()` has been updated to receive the field name as argument 6:

```php
public function __construct(
    ?string $name = null,
    array $options = [],
    array $fieldMapping = [],
    array $associationMapping = [],
    array $parentAssociationMappings = [],
    ?string $fieldName = null
) {
```

Deprecated `Sonata\AdminBundle\Admin\BaseFieldDescription::setFieldName()`.

UPGRADE FROM 3.82 to 3.83
=========================

### Deprecated `admin_pool` parameter in `sonata.admin.dashboard.top` and `sonata.admin.dashboard.bottom` block events.

This parameter will be removed in 4.0. If you are using it, you SHOULD inject `Pool` service instead.

### Deprecated global Twig `sonata_admin` variable

This variable has been deprecated in favor of `sonata_config` variable.

### Sonata\AdminBundle\Twig\GlobalVariables

This class has been deprecated without replacement.

### Sonata\AdminBundle\Model\ModelManagerInterface

Argument 2 of `Sonata\AdminBundle\Model\ModelManagerInterface::createQuery()` method has been removed.

### Sonata\AdminBundle\Admin\Pool

- `Sonata\AdminBundle\Admin\Pool::getTitle()` method has been deprecated.
  Use `Sonata\AdminBundle\SonataConfiguration::getTitle()` instead.
- `Sonata\AdminBundle\Admin\Pool::getTitleLogo()` method has been deprecated.
  Use `Sonata\AdminBundle\SonataConfiguration::getLogo()` instead.
- `Sonata\AdminBundle\Admin\Pool::getOption()` method has been deprecated.
  Use `Sonata\AdminBundle\SonataConfiguration::getOption()` instead.
- `Sonata\AdminBundle\Admin\Pool::getGroups()` method has been deprecated.
- `Sonata\AdminBundle\Admin\Pool::hasGroup()` method has been deprecated.
- `Sonata\AdminBundle\Admin\Pool::getAdminsByGroup()` method has been deprecated.

### Sonata\AdminBundle\Filter\Filter

Deprecate `Sonata\AdminBundle\Filter\Filter::setValue()` and `Sonata\AdminBundle\Filter\Filter::getValue()`
without replacement.

The implementation of the method `Sonata\AdminBundle\Filter\Filter::isActive()` will change from
```
public function isActive()
{
    $values = $this->value;

    return isset($values['value']) && false !== $values['value'] && '' !== $values['value'];
}
```
to
```
public function isActive()
{
    return $this->active;
}
```
in next major. Currently we are supporting both properties so you SHOULD start using `$this->active`.

### Sonata\AdminBundle\Admin\FieldDescriptionInterface

The following methods have been deprecated from the interface and will be added as abstract methods to
`Sonata\AdminBundle\Admin\BaseFieldDescription` in the next major version:
- `setFieldMapping()`
- `setAssociationMapping()`
- `setParentAssociationMappings()`
- `setMappingType()`

### Sonata\AdminBundle\Admin\BaseFieldDescription

Constructor has been modified to allow 3 more parameters
(`$fieldMapping`, `$associationMapping` and `$parentAssociationMapping`):

```php
public function __construct(
    ?string $name = null,
    array $options = [],
    array $fieldMapping = [],
    array $associationMapping = [],
    array $parentAssociationMappings = []
) {
```

Deprecated `Sonata\AdminBundle\Admin\BaseFieldDescription::setMappingType()`.

### Deprecated `AdminInterface::getValidator()` and  `AdminInterface::setValidator()` methods, `AbstractAdmin::$validator` property.

Methods are deprecated without replacement.

UPGRADE FROM 3.81 to 3.82
=========================

### Sonata\AdminBundle\Model\ModelManagerInterface

Argument 2 of `Sonata\AdminBundle\Model\ModelManagerInterface::createQuery()` method has been removed.

### Sonata\AdminBundle\Admin\Pool

- Passing a `Symfony\Component\PropertyAccess\PropertyAccessorInterface` instance as 4 argument instantiating
`Sonata\AdminBundle\Admin\Pool` is deprecated.
- `Sonata\AdminBundle\Admin\Pool::getPropertyAccessor()` method has been deprecated. You SHOULD inject `Symfony\Component\PropertyAccess\PropertyAccessorInterface`
where is needed.

### Sonata\AdminBundle\Action\SetObjectFieldValueAction

Not passing a `Symfony\Component\PropertyAccess\PropertyAccessorInterface` instance as argument 5 instantiating
`Sonata\AdminBundle\Action\SetObjectFieldValueAction` is deprecated.

### Sonata\AdminBundle\Admin\AdminHelper

Not passing a `Symfony\Component\PropertyAccess\PropertyAccessorInterface` instance as argument 1 instantiating
`Sonata\AdminBundle\Admin\AdminHelper` is deprecated.

### Sonata\AdminBundle\Twig\Extension\SonataAdminExtension

Argument 5 of `Sonata\AdminBundle\Admin\SonataAdminExtension` constructor SHOULD be a
`Symfony\Component\PropertyAccess\PropertyAccessorInterface` instance and argument 6 SHOULD be a
`Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface` instance or "null".

UPGRADE FROM 3.80 to 3.81
=========================

### Sonata\AdminBundle\Block\AdminSearchBlockService

Not passing the `empty_boxes` option as argument 4 to `Sonata\AdminBundle\Block\AdminSearchBlockService()` is deprecated.

### Deprecated `Sonata\AdminBundle\Admin\AdminInterface::validate()` method.

Use `Symfony\Component\Validator\Validation::validate()` instead.

### Deprecated `Sonata\AdminBundle\Admin\AbstractAdmin::attachInlineValidator()` method.

This method has been deprecated without replacement.

### Deprecated `Sonata\AdminBundle\Admin\AdminExtensionInterface::validate()` method.

This method has been deprecated without replacement.

UPGRADE FROM 3.79 to 3.80
=========================

### Sonata\AdminBundle\Form\Type\Operator\StringOperatorType

Added "Not equal" in the default list for "choices" option in order to allow filtering by strings that are not equal to the model data.

### Deprecated `Sonata\AdminBundle\Model\ModelManagerInterface::modelTransform()`

This method has been deprecated without replacement.

UPGRADE FROM 3.78 to 3.79
=========================

### Template registry structure and responsibilities.

The `Sonata\AdminBundle\Templating\TemplateRegistry` class has been splitted into 3 classes:
   - `TemplateRegistry`, implementing `Sonata\AdminBundle\Templating\TemplateRegistryInterface`
   - `MutableTemplateRegistry`, implementing `Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface`
   - `AbstractTemplateRegistry`, implementing `Sonata\AdminBundle\Templating\TemplateRegistryInterface`. You MUST extend this class if you want to create your own template registry.

The interface `Sonata\AdminBundle\Templating\TemplateRegistryAwareInterface` was updated in order to handle instances of `TemplateRegistryInterface`.
The interface `Sonata\AdminBundle\Templating\MutableTemplateRegistryAwareInterface` was added to provide a simple contract for classes depending on a `MutableTemplateRegistryInterface`.

`TemplateRegistry` will stop implementing `MutableTemplateRegistryInterface` in version 4.0. If you are using `setTemplate()` or `setTemplates()` methods, you MUST use `MutableTemplateRegistry` instead.

### Deprecated `Sonata\AdminBundle\Model\DatagridManagerInterface` interface.

This interface has been deprecated without replacement.

`ModelManagerInterface::getDefaultSortValues()` won't be used anymore.

### Empty values in datagrid filters

Empty values are passed to datagrid filters. If you have custom datagrid filters, you MUST add empty string checks to them.

```php
->add('with_open_comments', CallbackFilter::class, [
    'callback' => static function (ProxyQueryInterface $query, string $alias, string $field, array $value): bool {
        if (!$value['value']) {
            return false;
        }

        $query
            ->leftJoin(sprintf('%s.comments', $alias), 'c')
            ->andWhere('c.moderation = :moderation')
            ->setParameter('moderation', CommentModeration::APPROVED);

        return true;
    },
    'field_type' => CheckboxType::class,
]);
```

The `!$value['value']` check is required to avoid the filtering by `''` if you didn't used the filter.

UPGRADE FROM 3.77 to 3.78
=========================

### Deprecated `Sonata\AdminBundle\Model\ModelManagerInterface::getDataSourceIterator()`

As replacement, you MUST inject an instance of `Sonata\AdminBundle\Exporter\DataSourceInterface` in your admin. This way, the method `DataSourceInterface::createIterator()` will be used instead.

Setting a `DataSourceInterface` instance in your admin will be mandatory in version 4.0.

### Deprecated `Sonata\AdminBundle\Command\Validators::validateEntityName()`

In version 3.77, the shortcut notation for model class names (`AppBundle:User`)
has been deprecated in favor of its FQCN (`App\Model\User`) when passing `user_model`
option to `sonata:admin:generate-object-acl` command, so this method SHOULD not
be called if that deprecation is addressed.

### Deprecated not configuring `acl_user_manager` and using ACL security handler when `friendsofsymfony/user-bundle` is installed.

If you are using `friendsofsymfony/user-bundle` and using ACL security handler, you MUST explicitly configure the `acl_user_manager`.

```yaml
sonata_admin:
    security:
        acl_user_manager: App\Manager\AclFOSUserManager # this service MUST implement "AdminAclUserManagerInterface"
```

### Deprecated configuring `acl_user_manager` with a service that does not implement `AdminAclUserManagerInterface`.

Given this configuration:
```yaml
sonata_admin:
    security:
        acl_user_manager: 'App\Manager\AclUserManager'
```

`App\Manager\AclUserManager` MUST implement `AdminAclUserManagerInterface`, if you are using `fos_user_manager`, this could
be an example:
```php
<?php

namespace App\Manager;

use FOS\UserBundle\Model\UserManagerInterface;
use Sonata\AdminBundle\Util\AdminAclUserManagerInterface;

final class AclUserManager implements AdminAclUserManagerInterface
{
    /**
     * @var UserManagerInterface
     */
    private $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    public function findUsers(): iterable
    {
        return $this->userManager->findUsers();
    }
}
```

UPGRADE FROM 3.76 to 3.77
=========================

### Deprecated `Sonata\AdminBundle\Admin\Pool::getContainer()` method.

This method has been deprecated without replacement.

### Deprecated using shortcut notation when specifying the `user_model` option in `sonata:admin:generate-object-acl` command.

The shortcut notation (`AppBundle:User`) has been deprecated in favor of the FQCN (`App\Model\User`) when passing
`user_model` option to `sonata:admin:generate-object-acl` command.

UPGRADE FROM 3.75 to 3.76
=========================

## Deprecated `Sonata\AdminBundle\Datagrid\ProxyQueryInterface::getUniqueParameterId()`

This method has been deprecated without replacement.

## Deprecated `Sonata\AdminBundle\Datagrid\ProxyQueryInterface::entityJoin()`

This method has been deprecated without replacement.

UPGRADE FROM 3.74 to 3.75
=========================

## Deprecated `Sonata\AdminBundle\Controller\CRUDController::getRestMethod()` method

`Sonata\AdminBundle\Controller\CRUDController::getRestMethod()` method is deprecated.
Use `Symfony\Component\HttpFoundation\Request::getMethod()` instead.

## Deprecated `Sonata\AdminBundle\Model\ModelManagerInterface` collection-related methods.

Use:
- `new \Doctrine\Common\Collections\ArrayCollection()` instead of `getModelCollectionInstance($class)`
- `$collection->removeElement($element)` instead of `collectionRemoveElement($collection, $element)`
- `$collection->add($element)` instead of `collectionAddElement($collection, $element)`
- `$collection->contains($element)` instead of `collectionHasElement($collection, $element)`
- `$collection->clear()` instead of `collectionClear($collection)`

UPGRADE FROM 3.73 to 3.74
=========================

## Deprecated `Sonata\AdminBundle\Datagrid\ProxyQueryInterface::getSingleScalarResult`

Use `Sonata\AdminBundle\Datagrid\ProxyQueryInterface::execute` instead.

## The following templates have been deprecated

 - `src/Resources/views/CRUD/base_filter_field.html.twig`
 - `src/Resources/views/CRUD/base_inline_edit_field.html.twig`
 - `src/Resources/views/CRUD/base_standard_edit_field.html.twig`
 - `src/Resources/views/CRUD/edit_array.html.twig `
 - `src/Resources/views/CRUD/edit_boolean.html.twig`
 - `src/Resources/views/CRUD/edit_file.html.twig`
 - `src/Resources/views/CRUD/edit_integer.html.twig`
 - `src/Resources/views/CRUD/edit_sonata_type_immutable_array.html.twig`
 - `src/Resources/views/CRUD/edit_string.html.twig`
 - `src/Resources/views/CRUD/edit_text.html.twig`

## Deprecated `help` option in field description

You MUST use Symfony's [`help`](https://symfony.com/doc/4.4/reference/forms/types/form.html#help) option instead.

Before:
```php
$formMapper
    ->add('field', null, [], [
        'help' => 'Help text <small>Please!</small>',
    ])
    ->add('field2')
    ->addHelp('field2', 'This field is required.')
    ->add('field3')
    ->setHelps([
        'field3' => 'Great day to great work!',
    ]);
```

After:
```php
$formMapper
    ->add('field', null, [
        'help' => 'Help text <small>Please!</small>',
        'help_html' => true,
    ])
    ->add('field2', null, [
        'help' => 'This field is required.'
    ])
    ->add('field3', null, [
        'help' => 'Great day to great work!'
    ]);
```

### Upgrade to SonataBlockBundle 4.0

We added compatibility with SonataBlockBundle 4.0, make sure you are explicitly declaring your dependency
with `sonata-project/block-bundle` on your composer.json in order to avoid unwanted upgrades.

There is a minimal BC Break on `AdminListBlockService`, `AdminSearchBlockService` and `AdminStatsBlockService`. If you are extending those clases (keep in mind that they will become final on 4.0) you should add return type hints to `execute()` and `configureSettings()`.

## Deprecated passing `callable` that does not return `Symfony\Component\Routing\Route` as `$element` (2nd argument) to `Sonata\AdminBundle\Route\RouteCollection::addElement($code, $element)`

When calling a `Sonata\AdminBundle\Route\RouteCollection::addElement($code, $element)`, please pass `$element` of type `Route|callable():Route`. Passing `callable` that returns non instance of `Route` is deprecated.

UPGRADE FROM 3.72 to 3.73
=========================

### Deprecated `Sonata\AdminBundle\Model\ModelManagerInterface::getParentFieldDescription`

Use `Sonata\AdminBundle\Admin\AdminInterface::getParentFieldDescription` instead.

UPGRADE FROM 3.71 to 3.72
=========================

## Deprecated `SonataAdminBundle\Admin\AdminHelper::addNewInstance()`

Use
```
$instance = $fieldDescription->getAssociationAdmin()->getNewInstance();
SonataAdminBundle\Manipulator\ObjectManipulator::setObject($instance, $object, $fieldDescription);
```
Instead of
```
$this->adminHelper->addNewInstance($object, $fieldDescription);
```

The static method `setObject()` avoids the need to inject the admin helper dependency,
and adds more flexibility with the instance you're adding to the object.

UPGRADE FROM 3.68 to 3.69
=========================

## Deprecated `sonata_truncate` Twig filter

This filter has been deprecated in favor of the [`u` filter](https://twig.symfony.com/doc/2.x/filters/u.html):

## Deprecated `SonataAdminBundle\Twig\Extension\UnicodeString`

Use `Symfony\Component\String\UnicodeString` instead.

UPGRADE FROM 3.67 to 3.68
=========================

## Added constants for "show" and "list" templating types

You can use `TemplateRegistry` constants, like
```
$showMapper->add('foo', TemplateRegistry::TYPE_STRING)
```
instead of using directly a string value.
```
$showMapper->add('foo', 'string')
```

The list of available types can be found in [the documentation](docs/reference/field_types.rst).

## Deprecated templating types

- `text`: deprecated in favor of `TemplateRegistry::TYPE_STRING`
- `decimal`: deprecated in favor of `TemplateRegistry::TYPE_FLOAT`
- `smallint`: deprecated in favor of `TemplateRegistry::TYPE_INTEGER`
- `bigint`: deprecated in favor of `TemplateRegistry::TYPE_INTEGER`

UPGRADE FROM 3.66 to 3.67
=========================

## Deprecated accessing to a non existing value when adding field to `showMapper` and `listMapper`.

Before:
```php
$showMapper->add('nonExistingField');
$listMapper->add('nonExistingField');
```
was displaying nothing in the list and the show views without any warning or error.

But
```php
$formMapper->add('nonExistingField');
```
was throwing an exception.

In the next major an exception will be thrown if no getter/isser/hasser is found for the property. Since most
of the time the error is coming from a typo, this will allow the developer to catch it as fast as possible.
Currently this will only trigger a deprecation if the field value is not found.

UPGRADE FROM 3.65 to 3.66
=========================

## Deprecated not passing a `Sonata\AdminBundle\Admin\AdminHelper` instance to `Sonata\AdminBundle\Form\Type\AdminType::__construct()`

When instantiating a `Sonata\AdminBundle\Form\Type\AdminType` object, please use the 1 parameter signature `($adminHelper)`.

UPGRADE FROM 3.63 to 3.64
=========================

## Deprecated not setting as `false` the configuration option `sonata_admin.options.legacy_twig_text_extension`

This option controls which Twig text extension will be used to provide filters like
`truncate` or `wordwrap`.
The legacy behavior is provided by the abandoned package ["twig/extensions"](https://github.com/twigphp/Twig-extensions#twig-extensions-repository),
while the new implementation is based on ["twig/string-extra"](https://github.com/twigphp/string-extra).
Its default value is `true` in order to keep the legacy behavior. You should set
it to `false` in order to get the behavior which will be used by default at 4.0.

```yaml
sonata_admin:
    options:
        legacy_twig_text_extension: false
```

## Deprecated the `truncate.preserve` and `truncate.separator` options in views

You should use the `truncate.separator` and `truncate.cut` options instead. Unlike
`truncate.preserve`, `truncate.cut` has `false` as its default value and the opposite
behavior:

Before:
```php
$showMapper
    ->add('field', null, [
        'truncate' => [
            'preserve' => true,
            'separator' => '...',
        ],
    ])
;
```

After:
```php
$showMapper
    ->add('field', null, [
        'truncate' => [
            'cut' => false,
            'ellipsis' => '...',
        ],
    ])
;
```

UPGRADE FROM 3.59 to 3.60
=========================

## Deprecated not setting "sonata.admin.manager" tag in model manager services

If you are using [autoconfiguration](https://symfony.com/doc/4.4/service_container.html#the-autoconfigure-option),
all the services implementing `Sonata\AdminBundle\Model\ModelManagerInterface` will
be automatically tagged. Otherwise, you must tag them explicitly.

Before:
```xml
<service id="sonata.admin.manager.custom" class="App\Model\ModelManager">
    <!-- ... -->
</service>
```

After:
```xml
<service id="sonata.admin.manager.custom" class="App\Model\ModelManager">
    <!-- ... -->
    <tag name="sonata.admin.manager"/>
</service>
```

## Deprecated `sonata_help` option in form types

You should use Symfony's [`help`](https://symfony.com/doc/4.4/reference/forms/types/form.html#help) option instead.

Before:
```php
$formMapper
    ->add('field', null, [
        'sonata_help' => 'Help text',
    ])
;
```

After:
```php
$formMapper
    ->add('field', null, [
        'help' => 'Help text',
    ])
;
```

UPGRADE FROM 3.56 to 3.57
=========================

## Deprecated the use of string names to reference filters in favor of the FQCN of the filter.

Before:
```php
$datagridMapper
    ->add('field', 'filter_type')
;
```

After:
```php
use App\Filter\FilterType;

$datagridMapper
    ->add('field', FilterType::class)
;
```

UPGRADE FROM 3.51 to 3.52
=========================

## Deprecated `SonataAdminBundle\Controller\HelperController` in favor of actions

If you extended that controller, you should split your extended controller and
extend the corresponding classes in `SonataAdminBundle\Action\`.

## Deprecated `header_style` option

If you need to style headers prefer to use CSS classes and not in the html DOM.
In this case please use `header_class` option.

## Deprecated returning other type than `Collection` from `SimplePager::getResults()`

When calling `SimplePager::getResults()` on non-empty result which has set `$maxPerPage`, `Collection` would be returned instead of `array` as it is declared in `PagerInterface`. Update usage of `SimplePager::getResults()`, ensure you are transforming `Collection` to `array` and you aren't dealing with any of its methods.

```
// will return Collection on non-empty result and array on empty result
$results = $pager->getResults();

if ($results instanceof ArrayCollection) {
    $results = $results->toArray();
}
```

UPGRADE FROM 3.34 to 3.35
=========================

## Multiple parents

Admin classes can now have multiple parents, when registering the service
you should pass a field name:

```xml
<service id="sonata.admin.playlist" class="App\Admin\PlaylistAdmin">
    <!-- ... -->

    <call method="addChild">
        <argument type="service" id="sonata.admin.video" />
        <argument>playlist</argument>
    </call>
</service>
```

Overwriting `$parentAssociationMapping` is discouraged.

Deprecated calling of `AbstractAdmin::addChild` without second argument.

UPGRADE FROM 3.33 to 3.34
=========================

## Deprecated use of $templates in AbstractAdmin and Pool

The `AbstractAdmin::$templates` attribute and the methods `getTemplate()` and
`getTemplates()` are deprecated. Please use the new TemplateRegistry services
instead. One per admin is generated and available through the admin code +
`.template_registry` (for example, `app.admin.news` uses `app.admin.news.template_registry`).

The `Pool::$templates` attribute and the methods `getTemplate()`, `getTemplates()`
and `setTemplates()` are deprecated. Please use the TemplateRegistry service
`sonata.admin.global_template_registry` instead.

The Twig function `get_admin_pool_template()` is deprecated. Please use
`get_global_template()` instead.

## Deprecated AbstractAdmin::$persistFilters

The `AbstractAdmin::$persistFilters` is deprecated and should not be used anymore.
The problem was that it was not easy to change the way filters are persisted.
Instead of a simple boolean var (whether to persist or not filters) you can now inject a service,
that will be responsible for doing the job (see `FilterPersisterInterface`).
An implementation was added, which falls back to the previous behavior : `SessionFilterPersister`.

## Deprecated edit/show/delete of a child admin that does not belong to a given parent

This is not allowed anymore and will throw a 404 error in the future.

UPGRADE FROM 3.32 to 3.33
=========================

## Deprecated SonataAdminExtension::output()

The `SonataAdminExtension::output()` method is deprecated and should not be
used anymore.

UPGRADE FROM 3.30 to 3.31
=========================

## Deprecated AdminVoter::setRequest

The `AdminVoter::setRequest` is deprecated and should not be used anymore.

UPGRADE FROM 3.29 to 3.30
=========================

## Deprecated AbstractAdmin::addSubClass

This method was inconsistent with the structure of `AbstractAdmin::$subClasses`,
which is supposed to contain a hash that associates aliases with FQCNs. Use `AbstractAdmin::setSubClasses` instead.

UPGRADE FROM 3.27 to 3.28
=========================

## Deprecated ChildrenVoter and service

The feature provided by this class was replaced with something much more simple, and you should not rely on it anymore,
as well as on the `sonata.admin.menu.matcher.voter.children` service.

UPGRADE FROM 3.26 to 3.27
=========================

## Deprecated CRUDController::render()

Call `CRUDController::renderWithExtraParams()` instead.

UPGRADE FROM 3.23 to 3.24
=========================

## Deprecated AbstractAdmin::setBaseCodeRoute() and AbstractAdmin::baseCodeRoute property

The `AbstractAdmin::baseCodeRoute` property is no longer supported.
Please use the `AbstractAdmin::getBaseCodeRoute()` method instead.

The `AbstractAdmin::setBaseCodeRoute()` method is no longer supported.
There is no replacement for this method.
You can still use the `AbstractAdmin::setCode()` method to set the code
of an admin.

UPGRADE FROM 3.57 to 3.58
=========================

## Dropped generator commands

`sonata:admin:generate` was based on the SensioGeneratorBundle, which is
incompatible with Symfony 4 and is no longer maintained. Please use
`make:sonata:admin` instead.

UPGRADE FROM 3.20 to 3.21
=========================

## Deprecated ModelChoiceList class in favor of ModelChoiceLoader

The `ModelChoiceList` class is no longer supported. Please use the `ModelChoiceLoader` class instead.

The `ModelChoiceList` is usually used on the [`choice_list`](http://symfony.com/doc/2.8/reference/forms/types/choice.html#choice-list)
option of a `FormBuilder`. The `ModelChoiceLoader` class must be replaced on the
[`choice_loader`](http://symfony.com/doc/3.3/reference/forms/types/choice.html#choice-loader) option.

UPGRADE FROM 3.13 to 3.14
=========================

## Deprecated automatic annotation registration with JMSDiExtraBundle

Starting with version 4.0, SonataAdminBundle will no longer register
annotations with JMSDiExtraBundle automatically. Please add the following to
your config.yml to register the annotations yourself:


```yaml
jms_di_extra:
    annotation_patterns:
        - JMS\DiExtraBundle\Annotation
        - Sonata\AdminBundle\Annotation
```

### Exporter service and class

The `sonata.admin.exporter` is deprecated in favor of the `sonata.exporter.exporter` service.
To make this service available, you have to install `sonata-project.exporter` ^1.7
and enable the bundle as described in the documentation.

UPGRADE FROM 3.11 to 3.12
=========================

## Deprecated ModelsToArrayTransformer::$choiceList property

When instantiating a ModelsToArrayTransformer object, please use the 2 parameter signature ($modelManager, $class).

UPGRADE FROM 3.10 to 3.11
=========================

## Deprecated Pager::getFirstIndice() and Pager::getLastIndice()

Please use `Pager::getFirstIndex()` and `Pager::getLastIndex()` instead!

UPGRADE FROM 3.9 to 3.10
========================

## Deprecated passing no 3rd argument to GroupMenuProvider

Passing no 3rd argument to `Menu\Provider\GroupMenuProvider` is deprecated.
Pass `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface` as 3rd argument.

UPGRADE FROM 3.8 to 3.9
=======================

## Deprecated admin translator

The `$translator` property and the corresponding methods (`setTranslator`, `getTranslator`, `trans` and `transChoice`) in `AbstractAdmin` are deprecated.
Please use `CRUDController::trans` or twig templates instead.

UPGRADE FROM 3.4 to 3.5
=======================

## Deprecated injection of container to GlobalVariables

The `$container` property in `Twig/GlobalVariables` is deprecated.

## Deprecated ModelTypeList for rename

The `Sonata\AdminBundle\Form\Type\ModelTypeList` class is now deprecated.

Use `Sonata\AdminBundle\Form\Type\ModelListType` instead.

### Tests

All files under the ``Tests`` directory are now correctly handled as internal test classes.
You can't extend them anymore, because they are only loaded when running internal tests.
More information can be found in the [composer docs](https://getcomposer.org/doc/04-schema.md#autoload-dev).

UPGRADE FROM 3.2 to 3.3
=======================

## Deprecated AbstractAdmin method argument

The `$context` argument is deprecated and will be removed.
Instead of relying on it (and using a `switch` statement),
rely on an abstraction, and inject different implementations of this abstraction in different actions.
Here is [an example](https://github.com/sonata-project/SonataAdminBundle/pull/3247#issuecomment-217744025).

UPGRADE FROM 3.1 to 3.2
=======================

## Deprecated override of AbstractAdmin::getBatchActions

Since `AbstractAdmin::configureBatchActions` is present, you should not override `AbstractAdmin::getBatchActions`.

This method will be final in 4.0.

## Backward compatibility break for AbstractAdmin::getSubject()

Now `AbstractAdmin::getSubject()` return `null` or `object` of subject entity. Previously,
`AbstractAdmin::getSubject()` may return `false` if entity identifier not match regexp `/^[0-9A-Fa-f\-]+$/`.

UPGRADE FROM 3.0 to 3.1
=======================

## Deprecated Admin class

The `Admin` class is deprecated. Use `AbstractAdmin` instead.

## Deprecated AdminExtension class

The `AdminExtension` class is deprecated. Use `AbstractAdminExtension` instead.

## Deprecated template fallback mechanism

The Twig extension method that fallback to a default template when the specified one does not exist.
You can no longer rely on that and should always specify templates that exist.

## Deprecated AbstractAdmin methods
- `buildBreacrumbs` is deprecated, and no replacement is given, it will become an internal method.
- `getBreadcrumbs` is deprecated in favor of the homonym method of the `sonata.admin.breadcrumbs_builder` service.
- The breadcrumbs builder accessors are deprecated,
the `sonata.admin.breadcrumbs_builder` service should be used directly instead.
