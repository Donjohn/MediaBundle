Installation
============

Composer
~~~~~~~~

.. code:: bash

    composer require donjohn/media-bundle

Kernel
~~~~~~

Add this to your AppKernel.php

.. code:: php

        new Liip\ImagineBundle\LiipImagineBundle(),
        new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
        new Donjohn\MediaBundle\DonjohnMediaBundle(),

Routing
~~~~~~~

Add this to your routing.yml

.. code:: yaml

    #app/config/routing.yml
    donjohn_media:
        resource: "@DonjohnMediaBundle/Resources/config/routing.yml"

    _liip_imagine:
        resource: "@LiipImagineBundle/Resources/config/routing.xml"

Minimal configuration
~~~~~~~~~~~~~~~~~~~~~

Create a new class and extends it with
Donjohn:raw-latex:`\MediaBundle`:raw-latex:`\Media`

.. code:: php

    namespace YourBundle\Entity;
    use Donjohn\MediaBundle\Model\Media as BaseMedia;

    /**
     * @ORM\Table()
     * @ORM\Entity()
     */
    class YourMedia extends BaseMedia
    {
        /**
         * @var integer
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="IDENTITY")
        */
        protected $id;
    }

BaseMedia does not implement a Timestampable pattern since 2.3. Use you
prefered in the extended class

ex with KNPDoctrineBehaviorsBundle:

.. code:: php

    namespace AppBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Donjohn\MediaBundle\Model\Media as BaseMedia;
    use Knp\DoctrineBehaviors\Model as ORMBehaviors;


    /**
     * @ORM\Table()
     * @ORM\Entity()
     */
    class Media extends BaseMedia
    {
        use ORMBehaviors\Timestampable\Timestampable;
        /**
         * @var integer
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="IDENTITY")
        */
        protected $id;
    }

Add this to your config.yml

.. code:: yaml

    doctrine:
        dbal:
            types:
                json: Doctrine\DBAL\Types\JsonArrayType


    donjohn_media:
        upload_folder: /media

    liip_imagine:
        filter_sets:
            full:
                quality: 100
            thumbnail:
                quality: 75
                filters:
                    auto_rotate: ~
                    thumbnail: { size: [120, 120], mode: outbound }
             #add yours

See `LiipImagineBundle Configuration`_ for liip filters configuration

Optional configuration
~~~~~~~~~~~~~~~~~~~~~~

Change folder for uploaded files

.. code:: yaml

    donjohn_media:
        upload_folder: /AnotherFolder

    liip_imagine:
        resolvers:
            default:
                web_path:
                    cache_prefix: AnotherFolder/cache

You can change the template used to render the media foreach provider as
well as the allowed types. Or Disable the provider…

.. code:: yaml

    donjohn_media:
        providers:
            image: ##provider alias
                template: YouBundle:View:Twig.html.twig
                allowed_types: ['image/jpg']
                enabled: true #default

Restrict uploaded file size

.. code:: yaml

    donjohn_media:
        file_max_size: 500M

Providers
~~~~~~~~~

Available providers : - image - file

Usage
~~~~~

To insert a media in the twig, use the block with an optional filter
name, defined in the liip_imagine.filter

.. _LiipImagineBundle Configuration: http://symfony.com/doc/current/bundles/LiipImagineBundle/configuration.html
