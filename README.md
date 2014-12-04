

Sonata Form Builder
==========================

A Drag & Drop Form builder inspired from the Git package https://github.com/minikomi/Bootstrap-Form-Builder
and adapted for Sonata-Admin with bootstrap v3 and Jquery

You need Bootstrap v3 and Jquery on your Javascript/CSS requirements and a recent Sonata Admin installation.

##Install the bundle

Insert this line at the end of AppKernel.php in your symfony :

``` php

    new Pirastru\FormBuilderBundle\PirastruFormBuilderBundle()

```
Then from console run the command for build the database table :

```sh

    $php app/console doctrine:schema:update --force

```

Then put on your routing.yml or better on your routing_admin.yml :

```yml

sonata_form_builder:
    resource: '@PirastruFormBuilderBundle/Controller/FormBuilderController.php'
    type:     annotation

```

In order to have on your list of Admin entities the Form Builder you must put on your symfony configuration (config.yml or better a separated file sonata_admin.yml), inside of the directive of 'sonata_admin' the follow code :

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

In order to have on the list of blocks the Form Builder Block available on a page put on your configuration the follow line :

``` yml

sonata_block:
    ....
    blocks:
        ....
        pirastru_form_builder.block:

```


You must define on parameters.yml the email from

``` yml

    formbuilder_email_from:

```

Run the follow command on your console to install assets :

```sh

    $php app/console  assets:install

```


Finish >>>>>

##Todo
- Fields in differents sizes
- Translations

