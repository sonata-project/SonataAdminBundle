UPGRADE 4.x
===========

UPGRADE FROM 4.18 to 4.19
=========================

## BCLabelTranslatorStrategy

The BCLabelTranslatorStrategy is deprecated. Please use another label translator strategy or
implements your own directly in your project.

UPGRADE FROM 4.13 to 4.14
=========================

## FilterInterface

Not implementing `getFormOptions()` is deprecated, it will replace the `getRenderSettings()`
in next major. If you have an implementation this way:
```php
public function getRenderSettings(): array
{
    return [DefaultType::class, [
        'operator_type' => $this->getOption('operator_type'),
        'operator_options' => $this->getOption('operator_options'),
        'field_type' => $this->getFieldType(),
        'field_options' => $this->getFieldOptions(),
        'label' => $this->getLabel(),
    ]];
}
```
You can implement the `getFormOptions()` method this way:
```php
public function getFormOptions(): array
{
    return [
        'operator_type' => $this->getOption('operator_type'),
        'operator_options' => $this->getOption('operator_options'),
        'field_type' => $this->getFieldType(),
        'field_options' => $this->getFieldOptions(),
        'label' => $this->getLabel(),
    ];
}
```

UPGRADE FROM 4.12.0 to 4.13.0
=============================

## Batch action is relevant

Deprecate `batchAction%sIsRelevant` hook. You must handle the specific logic in your
batch action controller directly.

UPGRADE FROM 4.11.1 to 4.12.0
=============================

## Datetime picker assets

Datetime picker assets were moved from SonataAdminBundle to form-extensions.
Normally this should not affect you, unless you have modified
the default javascript and/or stylesheets
(remember that you can also add extra stylesheets or javascript using
`extra_stylesheets` and `extra_javascripts` to avoid this kind of issues):

Before
```yaml
    sonata_admin:
        assets:
            javascript:
                bundles/sonataadmin/app.js
                your_own.js
            stylesheets:
                bundles/sonataadmin/app.css
                your_own.css
```

After
```yaml
    sonata_admin:
        assets:
            javascript:
                bundles/sonataadmin/app.js
                bundles/sonataform/app.js
                your_own.js
            stylesheets:
                bundles/sonataadmin/app.css
                bundles/sonataform/app.css
                your_own.css
```

UPGRADE FROM 4.7 to 4.8
=======================

## Admin definitions

Deprecate passing the code, the model class and the controller in the arguments section.

Before
```yaml
    services:
        app.admin.car:
            class: App\Admin\CarAdmin
            tags:
                - { name: sonata.admin, manager_type: orm, group: Demo, label: Car }
            arguments:
                - admin_car
                - App\Entity\Car
                - App\Controller\CarAdminController
```
After
```yaml
    services:
        app.admin.car:
            class: App\Admin\CarAdmin
            tags:
                - { name: sonata.admin, code: admin_car, model_class: App\Entity\Car, controller: App\Controller\CarAdminController, manager_type: orm, group: Demo, label: Car }
```

UPGRADE FROM 4.0 to 4.1
=======================

### appendParentObject is called inside createNewInstance with child admins

In a child admin, if you were overriding `createNewInstance` and relying on sonata to provide the needed "parent" entity
to the instance, now you have to call `appendParentObject` manually.

Before:
```php
final class PostAdmin extends AbstractAdmin
{
    public function createNewInstance(): object
    {
        return new Post();
    }
}
```

After:
```php

final class PostAdmin extends AbstractAdmin
{
    public function createNewInstance(): object
    {
        $object = new Post();

         // set the post author if the parent admin is "AuthorAdmin"
        $this->appendParentObject($object);

        return $object;
    }
}
```
