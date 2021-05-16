<?php


namespace App\Services;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Uid\Uuid;

class FileUploaderHelper
{
    public const POST_FEATURED_IMAGE_FILE_DIR = 'posts/images/featured';
    public const POST_CONTENT_IMAGE_FILE_DIR = 'posts/images/content';

    private Filesystem $uploadsFilesystem;

    private LoggerInterface $logger;

    private MessageFlashHelper $flashHelper;

    /**
     * FileUploaderHelper constructor.
     * @param Filesystem $uploadsFilesystem
     * @param LoggerInterface $fileUpload
     * @param MessageFlashHelper $flashHelper
     */
    public function __construct(Filesystem $uploadsFilesystem, LoggerInterface $fileUpload, MessageFlashHelper $flashHelper)
    {
        $this->uploadsFilesystem = $uploadsFilesystem;
        $this->logger = $fileUpload;
        $this->flashHelper = $flashHelper;
    }

    public function uploadPostFeaturedImage(File $file, ?string $existingFilename = null): ?string
    {
        $newFilename = Uuid::v6()->toRfc4122() . '-' . uniqid() . '.' . $file->guessExtension();

        return $this->uploadFile($file, $newFilename, self::POST_FEATURED_IMAGE_FILE_DIR, $existingFilename);
    }

    public function uploadPostContentImage(File $file): ?string
    {
        $newFilename = Uuid::v6()->toRfc4122() . '-' . uniqid() . '.' . $file->guessExtension();

        return $this->uploadFile($file, $newFilename, self::POST_CONTENT_IMAGE_FILE_DIR);
    }

    public function deletePostFeaturedImage(string $featuredImage): void
    {
        $this->deleteFile(self::POST_FEATURED_IMAGE_FILE_DIR, $featuredImage);
    }

    private function uploadFile(File $file, string $newFilename, string $dir, ?string $existingFilename = null): ?string
    {
        $stream = fopen($file->getPathname(), 'r');

        try {
            $this->uploadsFilesystem->writeStream($dir . '/' . $newFilename, $stream);
        } catch (FilesystemException $e) {
            $this->logger->error(sprintf( // @codeCoverageIgnore
                'Impossible d\'enregistrer le fichier chargé "%s". Raison : %s', // @codeCoverageIgnore
                $newFilename, // @codeCoverageIgnore
                $e->getMessage()
            ));
            $this->flashHelper->addFlash('error', sprintf( // @codeCoverageIgnore
                'Impossible d\'enregistrer le fichier chargé "%s". Raison : %s', // @codeCoverageIgnore
                $newFilename, // @codeCoverageIgnore
                $e->getMessage()
            ));
            return null;
        }

        if (is_resource($stream)) {
            fclose($stream);
        }
        $this->logger->info(
            sprintf(
                'Fichier "%s" ajouté avec succès !',
                $newFilename
            )
        );

        if ($existingFilename) {
            $this->deleteFile($dir, $existingFilename);
        }

        return $newFilename;
    }

    private function deleteFile(string $dir, string $existingFilename): void
    {
        try {
            $this->uploadsFilesystem->delete($dir . '/' . $existingFilename);
            $this->logger->info(
                sprintf(
                    'Fichier "%s" supprimé avec succès !',
                    $existingFilename
                )
            );
        } catch (FilesystemException $e) {
            $this->logger->error( // @codeCoverageIgnore
                sprintf( // @codeCoverageIgnore
                    'Impossible de supprimer le fichier existant "%s" ' . // @codeCoverageIgnore
                    'existant lors de la suppression. Raison : %s', // @codeCoverageIgnore
                    $existingFilename, // @codeCoverageIgnore
                    $e->getMessage() // @codeCoverageIgnore
                )
            );
            $this->flashHelper->addFlash('error', sprintf( // @codeCoverageIgnore
                'Impossible de supprimer le fichier existant "%s" ' . // @codeCoverageIgnore
                'existant lors de la suppression. Raison : %s', // @codeCoverageIgnore
                $existingFilename, // @codeCoverageIgnore
                $e->getMessage() // @codeCoverageIgnore
            ));
        }
    }

}
