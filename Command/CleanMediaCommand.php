<?php

declare(strict_types=1);

namespace Donjohn\MediaBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class BaydayFixturesSimulateCommand.
 */
class CleanMediaCommand extends Command
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
     * @var string
     */
    private $mediaFolder;
    /**
     * @var string
     */
    private $rootFolder;

    /**
     * BaydayFixturesSimulateCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ProviderFactory        $providerFactory
     * @param string                 $mediaFolder
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ProviderFactory $providerFactory,
        string $mediaFolder,
        string $rootFolder
    ) {
        $this->entityManager = $entityManager;
        parent::__construct(self::$defaultName);
        $this->providerFactory = $providerFactory;
        $this->mediaFolder = $mediaFolder;
        $this->rootFolder = $rootFolder;
    }

    /** @var string */
    protected static $defaultName = 'media:clean';

    /** {@inheritdoc} */
    protected function configure(): void
    {
        $this
            ->setDescription('clean all ghosts files from media folder')
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

        /** @var ClassMetadata $metadata */
        foreach ($mediaClasses as $metadata) {
            $progressBarMetadatas->advance();
            $this->io->writeln('Traitement de '.$metadata->getName());

            /** @var Media[] $medias */
            $medias = $this->entityManager->getRepository($metadata->getName())->findAll();
            $progressBarMedias = new ProgressBar($this->io, count($medias));
            $progressBarMedias->start();
            /** @var Media $media */
            foreach ($medias as $media) {
                $progressBarMedias->advance();
                $provider = $this->providerFactory->getProvider($media);

                $filePath = $provider->getMediaFilesystem()->getFullPath($media);
                $destPath = pathinfo(str_replace($this->mediaFolder, DIRECTORY_SEPARATOR.'tmpMedia', $filePath), PATHINFO_DIRNAME);
                $file = new File($filePath);
                $file->move($destPath, $file->getFilename());
            }
            $progressBarMedias->finish();
        }
        $progressBarMetadatas->finish();

        $this->io->writeln('On efface tous les fichiers de '.$this->rootFolder.$this->mediaFolder.DIRECTORY_SEPARATOR);
        $this->deleteFiles($this->rootFolder.$this->mediaFolder);

        $this->io->writeln('On remets les valides.');
        rename($this->rootFolder.DIRECTORY_SEPARATOR.'tmpMedia', $this->rootFolder.$this->mediaFolder);
        /* @noinspection MkdirRaceConditionInspection */
        @mkdir($this->rootFolder.$this->mediaFolder.DIRECTORY_SEPARATOR.'image');
        $this->io->text('DONE !!!');
    }

    /**
     * @param $target
     */
    private function deleteFiles($target): void
    {
        if (is_dir($target)) {
            $files = glob($target.'*', GLOB_MARK); //GLOB_MARK adds a slash to directories returned

            foreach ($files as $file) {
                $this->deleteFiles($file);
            }

            @rmdir($target);
        } elseif (is_file($target)) {
            unlink($target);
        }
    }
}
