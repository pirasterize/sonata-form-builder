

Sonata Form Builder
==========================

A Drag & Drop Form builder inspired from the Git package https://github.com/minikomi/Bootstrap-Form-Builder
and adapted for Sonata-Admin with bootstrap v3 and Jquery

You need Bootstrap v3 and Jquery on your Javascript/CSS requirements and a recent Sonata Admin installation.

##Install the bundle

1. You can download and put on your /src directory (like this  src/Pirastru/FormBuilderBundle)
or insert on your vendor directory with the follow command :

```sh

 php -dmemory_limit=1G ./composer.phar require  pirasterize/sonata-form-builder

```
If you have a >minimum-stability< error is because your symfony installation accept only stable packages.
Edit composer.json file and change "minimum-stability" from "stable" to "dev" :

```
    ...
    "minimum-stability": "dev",
    ...

```


2. Insert the follow line on your AppKernel.php in your symfony :

```php

    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...

                 new Pirastru\FormBuilderBundle\PirastruFormBuilderBundle()
            );

            // ...
        }

        // ...
    }
```


3. Then on console run the command for build the database table :

```sh

    $php app/console doctrine:schema:update --force

```

4. Then put on your app/config/routing.yml or better to separate app/config/routing_admin.yml :

```yml

sonata_form_builder:
    resource: '@PirastruFormBuilderBundle/Controller/FormBuilderController.php'
    type:     annotation

```

5. In order to have on your list of Admin entities the Form Builder you must put on your configuration file (this case app/config/sonata/sonata_admin.yml), inside of the directive of 'sonata_admin' the follow code :

```yml

sonata_admin:
    ....
    dashboard:
        ....
        groups:
            ...
            sonata.admin.group.formbuilder:
                label: Form Builder
                items:
                    - pirastru_form_builder.admin

```

6. In order to have on the list of blocks the Form Builder Block available on a page put on your configuration file (app/config/sonata/sonata_block.yml) the follow line :

``` yml

sonata_block:
    ....
    blocks:
        ....
        pirastru_form_builder.block:

```


7. You must define on parameters.yml the email from

``` yml

    formbuilder_email_from: jeanmichel@basquiat.com

```

8. Run the follow command on your console to install assets :

```sh

    $php app/console  assets:install

```



##To see the result

Check on your Sonata Admin Entities you should see the 'Form Builder' Menu than create a new drag&drop form.
After that create a page from sonata page or edit an existing one and put from the list of available blocks the block called "Form Builder Drag&Drop".
On Options choose the Form you just created, and take a look on the page.



##Todo
- Fields in differents sizes
- Translations
- set dinamically Submit button label 
- tests

