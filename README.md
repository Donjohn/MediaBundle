[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2d30fe72-c272-4072-a843-17d798b65416/mini.png)](https://insight.sensiolabs.com/projects/2d30fe72-c272-4072-a843-17d798b65416)

Installation
=============


### Composer

```
composer require donjohn/media-bundle
```

### Kernel

Add this to your AppKernel.php

```PHP
    new Liip\ImagineBundle\LiipImagineBundle(),
    new Symfony\Bundle\AsseticBundle\AsseticBundle(),
    new Knp\DoctrineBehaviors\Bundle\DoctrineBehaviorsBundle(),    
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
```
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
     * @Groups({"api_output"})
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
```
donjohn_media:
    upload_folder: /AnotherFolder

liip_imagine:
    resolvers:
        default:
            web_path:
                cache_prefix: AnotherFolder/cache
```

Change media template used by providers
```
donjohn_media:
    providers:
        image: ##provider alias
            template: YouBundle:View:Twig.html.twig
```

Restrict uploaded file size
```
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
```
{% media mediaObject, '<filter>' %}
```
You can also pass class/width/height/alt options to the media rendering:
```
{% media mediaObject, '<filter>' with {class: 'classwanted class2wanted', alt: 'title', width: '200px', height: '50px'} %}
```


You can also ask for the path directly
```
{% path media, '<filter>' %}
```

In order to download a media, pls use the following to get the download link 
```
example:
<a href="{% download media%}">Download</a>
```


### FormType
An Donjohn\MediaBundle\Form\Type\MediaType is available
'provider' option default value is 'file', change it if you wanna create a media with another provider (ex 'image').
In case you're editing a persisted media object, the option is overwritten by $media->getProviderName() value in any case
```
$builder->add(<fieldName>, MediaType::class, array('provider'=> 'image' ) );
```

Set 'allow_delete' option to false if you don't want to allow removing media from an entity. It removes the unlink checkbox in the form.
  
If you want to upload a collection of Medias use the MediaCollection formType. The provider option is still available.
```
$builder->add(<fieldName>, MediaCollectionType::class );
```

### OneupUploader
For very large files, the bundle includes the Fine Uploader feature thanks to OneUpUploaderBundle.
```
$builder->add(<fieldName>, MediaFineUploaderType::class );
```
Don't forget to install the front part 
```
bower install fine-uploader --save 
```
include the css/js in your layout (fix path if needed). 
```twig
<link href="{{ asset('components/fine-uploader/dist/fine-uploader-gallery.css') }}" rel="stylesheet">
<script type="text/javascript" src="{{ asset('components/fine-uploader/dist/fine-uploader.min.js') }}"></script>
```
A bootstrap template is provided (or use the default one, see to the official documentation), add this line to the javascript section of your layout.
```
<script type="text/template" id="donjohn-media">
{{ render(controller('DonjohnMediaBundle:FineUploader:renderFineUploaderTemplate'))|raw }}
</script>
```

Add the OneupUploaderBundle to your AppKernel.php
```PHP
    new Oneup\UploaderBundle\OneupUploaderBundle(),
```
And to config.yml, add:
```
oneup_uploader:
    chunks:
        maxage: 86400
        storage:
            directory: %kernel.cache_dir%/uploader/chunks
    orphanage:
        maxage: 86400
    mappings:
        medias:
            namer:  donjohn.oneup_uploader.namer.original
            use_orphanage: true
            frontend: fineuploader            
            enable_cancelation: true
```
You can change the uploaded chunk size
```
donjohn_media:
    chunk_size: 50M #default
```

### Api
This bundle is compatible with DunglasApiBundle and NelmioApiDocBundle. No config is needed.
2 api groups are already defined for input and output serialization (api_input and api_output). If you want to change the groups or add new one. Modify the @Groups annotation in your extended class

```
namespace YourBundle\Entity;
use Donjohn\MediaBundle\Model\Media as BaseMedia;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class YourMedia extends BaseMedia
{
    /**
     * @ORM\Column(type="string", nullable=false)
     * @Groups({"new_group_input","another_group_input","new_group_ouput"})
     */
    protected $name;
}
```

and the in the config.yml, modify the configuration

```
donjohn_media:
    ...
    api:
        group_input: ['new_group_input', 'another_group_input']
        group_output: ['new_group_ouput']
            
```

### Custom MediaProvider
To implement your own provider, use the ProviderInterface or extends the BaseProvider (easier) 
then defined it as a service with the tag media.provider (beware, the alias must be the same as YourProvider->getAlias())

```
app.media.your_type.provider:
        class: YouApp\YourBundle\YourProvider
        tags:
            - { name: media.provider, alias: file }
``` 


### Javascript
The bundle is jquery dependant, you must add it before the media.js provided
```
<script src="{{ asset('bundles/donjohnmedia/js/media.js') }}"></script>
```
In case you don't want the awesome javascript feature, set the "mediazone" option to false in either MediaType or MediaCollectionType. You will fall back to raw file inputs


### Bootstrap
The bundle is bootstrap dependant, you must add it in your layout
