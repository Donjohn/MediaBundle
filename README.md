[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2d30fe72-c272-4072-a843-17d798b65416/mini.png)](https://insight.sensiolabs.com/projects/2d30fe72-c272-4072-a843-17d798b65416)

Installation instruction
===================
First, give credits to Sonata, their inspire this bundle.

### Composer

Type:
```
composer require donjohn/media-bundle
```

### Kernel

Add thoses bundles to your AppKernel.php

```PHP
    new Liip\ImagineBundle\LiipImagineBundle(),
    new Symfony\Bundle\AsseticBundle\AsseticBundle(),
    new Knp\DoctrineBehaviors\Bundle\DoctrineBehaviorsBundle(),
    new Donjohn\MediaBundle\DonjohnMediaBundle(),
```

### Routing

Add thoses route to your routing.yml

```yaml
#app/config/routing.yml
donjohn_media:
    resource: "@DonjohnMediaBundle/Resources/config/routing.yml"
```
    
    
### Config

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


Then add these lines to your config.yml
```yaml

doctrine:
    dbal:
        types:
            json: Doctrine\DBAL\Types\JsonArrayType
            
            
donjohn_media:
    upload_folder: /media
    entity: YourBundle\Entity\YourMedia

liip_imagine:
    filter_sets: #example set, define yours
        full: 
            quality: 100
        thumbnail:
            quality: 75
            filters:
                auto_rotate: ~
                thumbnail: { size: [120, 120], mode: outbound }
```

see [LiipImagineBundle Configuration](http://symfony.com/doc/current/bundles/LiipImagineBundle/configuration.html) for liip filters config


If you want another folder for your uploads, don't forget to modify liip setting as well
```
donjohn_media:
    upload_folder: /AnotherFolder

liip_imagine:
    resolvers:
        default:
            web_path:
                cache_prefix: AnotherFolder/cache
```

You can also change the template used to render a media, change the config:
```
donjohn_media:
    providers:
        image: ##provider alias
            template: YouBundle:View:Twig.html.twig
```

By default, php.ini setting is maximum but if you want to restrict a maximum uploaded file size, change the setting:
```
donjohn_media:
    file_max_size: 500M
```


###Providers
For the moment only Image (alias 'image') and File (alias 'file') provider are available.



### Twig
To insert a media in the twig, use the block with an optionnal filter name, defined in the liip_imagine.filter_sets section.
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
  
If you want to uplod a collection of Medias use the MediaCollection formType. The provider option is still available.
```
$builder->add(<fieldName>, MediaCollectionType::class );
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
