[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2d30fe72-c272-4072-a843-17d798b65416/mini.png)](https://insight.sensiolabs.com/projects/2d30fe72-c272-4072-a843-17d798b65416)

Give credits to Sonata, they inspired this bundle.

Installation
=============


### Composer

```bash
composer require donjohn/media-bundle
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

### LiipImagineBundle
Add this to your config.yml
```yaml
liip_imagine:
    filter_sets:
        full: 
            quality: 100
        thumbnail:
            quality: 75
            filters:
                auto_rotate: ~
                thumbnail: { size: [120, 120], mode: outbound }
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


### FormType
An Donjohn\MediaBundle\Form\Type\MediaType is available
```php
$builder->add(<fieldName>, MediaType::class, ['media_class'=> YourEntity::class] );
```

provider option default value is null. A guesser will try on the fly to detect the best provider fo each file unless you define the option. The default guess is 'file'.

Set 'allow_delete' option to false if you don't want to allow removing media from an entity. It removes the unlink checkbox in the form.

Set 'create_on_update' option to true if you don't want to update the current media when uploading a file but rather create a new media instead. Old one is not removed.
  
If you want to upload a collection of Medias set multiple to true.
```php
$builder->add(<fieldName>, MediaType::class, ['media_class' => YourEntity::class, 'multiple' => true ] );
```

### OneupUploader
For very large files, the bundle includes the Fine Uploader feature thanks to OneUpUploaderBundle.
```php
$builder->add(<fieldName>, MediaType::class, , ['media_class' => YourEntity::class, 'fine_uploader' => true, 'multiple' => <true|false> ] );
```
Don't forget to install fineuploader (bower/npm/...) and include the css/js in your layout (fix path if needed). 


Add the OneupUploaderBundle to your AppKernel.php
```PHP
    new Oneup\UploaderBundle\OneupUploaderBundle(),
```
And to config.yml, add:
```yaml
# Read the documentation: https://github.com/1up-lab/OneupUploaderBundle/blob/master/Resources/doc/index.md
oneup_uploader:
    chunks:
        storage:
            directory: "%kernel.cache_dir%/uploader/chunks"
    mappings:
        donjohn_media:
            namer: Donjohn\MediaBundle\Uploader\Naming\OriginalNamer
            use_orphanage: true
            frontend: fineuploader

```



You can change the uploaded chunk size or the template used to render the fineuploader frame
```yaml
donjohn_media:
    chunk_size: 50M #default
    fine_uploader_template: YourFineUploaderTempalte.twig.html
```


### Custom MediaProvider
To implement your own provider, extends the BaseProvider and redefine abstract function.  
Autowiring should do the job...



### Api platform
The bundle is compatible with [APIPlatform](https://api-platform.com/).
