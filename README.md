Prototype to easily create uniform backend application

## Installation

### Add BaseApplicationBundle to your src/Bundle dir

    git submodule add git@github.com:sonata-project/BaseApplicationBundle.git src/Bundle/BaseApplicationBundle

### Add EasyExtendsBundle to your application kernel

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Bundle\BaseApplicationBundle\BaseApplicationBundle(),
            // ...
        );
    }


### Add this line into your config.yml file 

    base_application.config: ~
