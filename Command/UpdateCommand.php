<?php

declare(strict_types=1);

namespace Donjohn\MediaBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Donjohn\MediaBundle\Filesystem\MediaFilesystemInterface;
use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class UpdateCommand.
 */
class UpdateCommand extends Command
{
    /** @var EntityManagerInterface $doctrine */
    private $entityManager;

    /** @var SymfonyStyle $io */
    private $io;
    /**
     * @var ProviderFactory
     */
    private $providerFactory;
    /**
     * @var MediaFilesystemInterface
     */
    private $mediaLocalFilesystem;

    /**
     * BaydayFixturesSimulateCommand constructor.
     *
     * @param EntityManagerInterface   $entityManager
     * @param ProviderFactory          $providerFactory
     * @param MediaFilesystemInterface $mediaLocalFilesystem
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ProviderFactory $providerFactory,
        MediaFilesystemInterface $mediaLocalFilesystem
    ) {
        parent::__construct(self::$defaultName);
        $this->entityManager = $entityManager;
        $this->providerFactory = $providerFactory;
        $this->mediaLocalFilesystem = $mediaLocalFilesystem;
    }

    /** @var string */
    protected static $defaultName = 'media:update';

    /** {@inheritdoc} */
    protected function configure(): void
    {
        $this
            ->setDescription('update files is filename not unique')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        $mediaClasses = array_filter($this->entityManager->getMetadataFactory()->getAllMetadata(), function ($metadata) {
            /* @var ClassMetadata $metadata */
            return $metadata->getReflectionClass()->isSubclassOf(Media::class);
        });

        $progressBarMetadatas = new ProgressBar($this->io, count($mediaClasses));
        $progressBarMetadatas->start();

        $mediasToDelete = [];

        /** @var ClassMetadata $metadata */
        foreach ($mediaClasses as $metadata) {
            $progressBarMetadatas->advance();
            $this->io->writeln('Traitement de '.$metadata->getName());

            /** @var Media[] $medias */
            $mediaFileNames = $this->entityManager->createQueryBuilder()->addSelect('m.filename')
                                    ->from($metadata->getName(), 'm')
                                    ->addGroupBy('m.filename')
                                    ->having('COUNT(m.filename) > 1')
                                    ->getQuery()
                                    ->getResult();

            $progressBarMedias = new ProgressBar($this->io, count($mediaFileNames));
            $progressBarMedias->start();
            /* @var Media $media */
            foreach ($mediaFileNames as $mediaFileName) {
                $medias = $this->entityManager->getRepository($metadata->getName())->findBy(['filename' => $mediaFileName['filename']]);

                foreach ($medias as $media) {
                    $provider = $this->providerFactory->getProvider($media);

                    try {
                        $file = new File($provider->getMediaFilesystem()->getFullPath($media));

                        $media->setFilename(sha1($media->getName().uniqid('', true)).'.'.pathinfo($media->getOriginalFilename(), PATHINFO_EXTENSION));
                        $finalPath = $file->getPath().$media->getFilename();

                        $this->io->writeln($file->getRealPath().' => '.$finalPath);

                        $this->mediaLocalFilesystem->createOrGetFilesystem()->copy($file->getRealPath(), $finalPath);

                        $this->entityManager->persist($media);
                        $this->entityManager->flush();
                    } catch (FileNotFoundException $exception) {
                        $this->io->writeln('le fichier '.$media->getFilename().' est introuvable : lancez la commande de clean');
                    }
                }
            }
            $progressBarMedias->finish();
        }
        $progressBarMetadatas->finish();

        $this->io->text('DONE !!!');
    }
}
