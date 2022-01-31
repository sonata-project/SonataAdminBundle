UPGRADE 4.x
===========

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
