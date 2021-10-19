UPGRADE 4.x
===========

UPGRADE FROM 4.0.1 to 4.0.2
===========================

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
