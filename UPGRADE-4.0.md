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

* `Sonata\Admin\AbstractAdmin::getActionButtons`
* `Sonata\Admin\AbstractAdmin::getBatchActions`
* `Sonata\Admin\AbstractAdmin::urlize`
* `Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass`
* `Sonata\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass`
* `Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass`
* `Sonata\AdminBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass`
* `Sonata\AdminBundle\DependencyInjection\Configuration`
* `Sonata\AdminBundle\DependencyInjection\SonataAdminExtension`
* `Sonata\AdminBundle\Translator\Extractor\JMSTranslatorBundle\AdminExtractor`
* `Sonata\AdminBundle\Twig\Extension`

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

## BreadcrumbsBuilder
The `buildBreacrumbs` method may no longer be called from outside the class.

## BreadcrumbsBuilderInterface
The `buildBreacrumbs` method has been removed from the interface.

## SonataAdminExtension
The Twig filters that come with the bundle will no longer load a default template when used with a missing template.
The `sonata_admin` twig extension is now final. You may no longer extend it.

## SimplePager
Method `SimplePager::getResults` is always returning an array

## WebpackEncoreBundle

In `config/sonata_admin.yaml` the complete `assets:` section was removed and is no longer available.

Execute `bin/console assets:install` and add following to the `config/sonata_admin.yaml` file:

```yaml
webpack_encore:
    builds:
        sonata_admin: '%kernel.project_dir%/public/bundles/sonataadmin/dist'
```
