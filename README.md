[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2d30fe72-c272-4072-a843-17d798b65416/mini.png)](https://insight.sensiolabs.com/projects/2d30fe72-c272-4072-a843-17d798b65416)

Give credits to Sonata, they inspired this bundle.

Installation
=============


### Composer

```bash
composer require donjohn/media-bundle
```

### Kernel

Add this to your AppKernel.php

```PHP
    new Liip\ImagineBundle\LiipImagineBundle(),
    new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
    new Donjohn\MediaBundle\DonjohnMediaBundle(),
```

### Routing

Add this to your routing.yml

```yaml
#app/config/routing.yml
donjohn_media:
    resource: "@DonjohnMediaBundle/Resources/config/routing.yml"
```
    
    
### Minimal configuration

Create a new class and extends it with Donjohn\MediaBundle\Media
```php
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
```
BaseMedia does not implement a Timestampable pattern since 2.3. Use you prefered in the extended class  

ex with KNPDoctrineBehaviorsBundle:
```php
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
```

Add this to your config.yml
```yaml
doctrine:
    dbal:
        types:
            json: Doctrine\DBAL\Types\JsonArrayType
            
            
donjohn_media:
    upload_folder: /media
    entity: YourBundle\Entity\YourMedia

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
```

See [LiipImagineBundle Configuration](http://symfony.com/doc/current/bundles/LiipImagineBundle/configuration.html) for liip filters configuration

### Optional configuration

Change folder for uploaded files
```yaml
donjohn_media:
    upload_folder: /AnotherFolder

liip_imagine:
    resolvers:
        default:
            web_path:
                cache_prefix: AnotherFolder/cache
```

You can change the template used to render the media foreach provider as well as the allowed types. Or Disable the provider...
```yaml
donjohn_media:
    providers:
        image: ##provider alias
            template: YouBundle:View:Twig.html.twig
            allowed_types: ['image/jpg']
            enabled: true #default
```

Restrict uploaded file size
```yaml
donjohn_media:
    file_max_size: 500M
```


### Providers
Available providers :
 - image
 - file


### Usage
To insert a media in the twig, use the block with an optional filter name, defined in the liip_imagine.filter_sets section.
If you don't provider a filter name, 'reference' filter is default. it will return the original media uploaded with any filter or post processing.
```twig
{% media mediaObject, '<filter>' %}
```
You can also pass class/width/height/alt options to the media rendering:
```twig
{% media mediaObject, '<filter>' with {class: 'classwanted class2wanted', alt: 'title', width: '200px', height: '50px'} %}
```


You can also ask for the path directly
```twig
<a href="{% path media, '<filter>' %}">{{ media.name }}</a>
or 
<a href="{{ media|mediaPath('<filter>') ">{{ media.name }}</a>
```

In order to download a media, pls use the following to get the download link 
```twig
<a href="{% download media%}">Download</a>
or
<a href="{{path('donjohn_media_download', {id: media.id})}}">Download</a>
```


### FormType
An Donjohn\MediaBundle\Form\Type\MediaType is available
```php
$builder->add(<fieldName>, MediaType::class ) );
```

provider option default value is null. A guesser will try on the fly to detect the best provider fo each file until you force by yourself the option. The default guess is 'file'.
In case you're editing a persisted media object, the option is overwritten by $media->getProviderName() value in any case
```php
$builder->add(<fieldName>, MediaType::class, array('provider'=> 'image' ) ); //to force file to be process with ImageProvider
```

Set 'allow_delete' option to false if you don't want to allow removing media from an entity. It removes the unlink checkbox in the form.
  
If you want to upload a collection of Medias use the MediaCollection formType. The provider option is still available.
```php
$builder->add(<fieldName>, MediaCollectionType::class );
```

### OneupUploader
For very large files, the bundle includes the Fine Uploader feature thanks to OneUpUploaderBundle.
```php
$builder->add(<fieldName>, MediaFineUploaderType::class );
```
Don't forget to install the front part 
```bash
bower install fine-uploader --save 
```
include the css/js in your layout (fix path if needed). 
```twig
<link href="{{ asset('components/fine-uploader/dist/fine-uploader-gallery.css') }}" rel="stylesheet">
<script type="text/javascript" src="{{ asset('components/fine-uploader/dist/fine-uploader.min.js') }}"></script>
```
A bootstrap template is provided (or use the default one, see to the official documentation), add this line to the javascript section of your layout.
```twig
<script type="text/template" id="donjohn-media">
{{ render(controller('DonjohnMediaBundle:FineUploader:renderFineUploaderTemplate'))|raw }}
</script>
```

Add the OneupUploaderBundle to your AppKernel.php
```PHP
    new Oneup\UploaderBundle\OneupUploaderBundle(),
```
And to config.yml, add:
```yaml
oneup_uploader:
    chunks:
        maxage: 86400
        storage:
            directory: "%kernel.cache_dir%/uploader/chunks"
    orphanage:
        maxage: 86400
    mappings:
        medias:
            namer:  Donjohn\MediaBundle\Uploader\Naming\OriginalNamer
            use_orphanage: true
            frontend: fineuploader            
            enable_cancelation: true
```
You can change the uploaded chunk size or the template used to render the fineuploader frame
```yaml
donjohn_media:
    chunk_size: 50M #default
    fine_uploader_template: YourFineUploaderTempalte.twig.html
```


### Custom MediaProvider
To implement your own provider, extends the BaseProvider and redefine getAlias and add configuration in the providers_ext path in config.yml
```yaml
donjohn_media:
    providers_ext:
        custom: ##provider alias
            template: YouBundle:View:Twig.html.twig
            allowed_types: ['application/custom']
```
Autowiring should do the job...


### Javascript
The bundle is jquery dependant, you must add it before the media.js provided
```twig
<script src="{{ asset('bundles/donjohnmedia/js/media.js') }}"></script>
```
In case you don't want the awesome javascript feature, set the "mediazone" option to false in either MediaType or MediaCollectionType. You will fall back to raw file inputs


### Bootstrap
The bundle is bootstrap dependant, you must add it in your layout


### Api platform
The bundle is compatible with [APIPlatform](https://api-platform.com/).
