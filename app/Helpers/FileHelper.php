<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileHelper
{
    /**
     * Gets all the files of the given directory.
     *
     * Searches all files even if there is files inside folders.
     *
     * @param string $path
     * @return array
     */
    public static function getDirectoryFilesRecursive(string $path): array
    {
        $recursiveIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        $result = [];
        foreach ($recursiveIterator as $file) {
            if ($file->isDir()) {
                continue;
            }
            $fileName = $file->getPathname();
            $result[] = str_replace($path . '\\', '', $fileName);
        }

        return $result;
    }

    /**
     * Creates a public temp url form a path.
     *
     * @param string $path
     * @param int $time in minutes
     * @param bool $trimDomain
     * @return string
     *
     */
    public static function GcsTempUrl(string $path, int $time = 15, bool $trimDomain = true): string
    {
        if (env('APP_ENV') === 'testing') {
            return 'http://some-signed-url.test';
        }

        if ($trimDomain) {
            $path = self::removeDomainFromGcsFile($path);
        }

        if ($path[0] == '/') {
            $path = substr($path, 1);
        }

        $exists = Storage::disk('gcs')->exists($path);

        if ($exists) {
            try {
                return Storage::disk('gcs')->temporaryUrl($path, now()->addMinutes($time));
            } catch (\Exception $exception) {
                throw new \Exception("The temporary url for the file $path cannot be generated.");
            }
        } else {
            throw new \Exception("The file $path does not exists.");
        }
    }

    /**
     * Return the public path of the GCS file.
     *
     * @param string $path
     * @return string
     */
    public static function GcsPublicUrl(string $path): string
    {
        if ($path[0] == '/') {
            $path = substr($path, 1);
        }

        return Storage::disk('gcs')->url($path);
    }

    /**
     * Return the public path of the GCS file.
     *
     * @param string $path
     * @return string
     */
    public static function GcsGetContent(string $path): string
    {
        if ($path[0] == '/') {
            $path = substr($path, 1);
        }

        return Storage::disk('gcs')->get($path);
    }

    /**
     * Returns the mime type of the given document.
     *
     * @param string $disk
     * @param string $path
     * @param bool $trimDomain
     * @return string|null
     */
    public static function getMimeType(string $disk, string $path, bool $trimDomain = true): string|null
    {
        if (env('APP_ENV') === 'testing') {
            return 'application/pdf';
        }
        if ($trimDomain) {
            $path = self::removeDomainFromGcsFile($path);
        }
        $mimeType = Storage::disk($disk)->mimeType($path);
        if ($mimeType !== false) {
            return $mimeType;
        }

        return null;
    }

    /**
     * Remove the domain from aws file.
     *
     * @param string $path
     * @return string
     */
    public static function removeDomainFromGcsFile(string $path): string
    {
        $position = strpos($path, 'com');
        if ($position !== false) {
            return substr($path, $position + 4);
        }

        return $path;
    }

    /**
     * Move file to another path.
     *
     * @param string $currentPath
     * @param string $newPath
     * @return bool
     *
     */
    public static function moveDestinationFile(string $currentPath, string $newPath): bool
    {
        $exists = Storage::disk('gcs')->exists($currentPath);

        if (!$exists) {
            throw new \Exception("The file $currentPath does not exists.");
        }

        $stored = Storage::disk('gcs')->move(
            $currentPath,
            $newPath
        );

        return $stored;
    }

    /**
     * Store file.
     *
     * @param string $filePath
     * @param string $fileContent
     * @return bool
     */
    public static function storeFile(string $filePath, string $fileContent): bool
    {
        $stored = Storage::disk('gcs')->put(
            $filePath,
            $fileContent,
        );
        if (!$stored) {
            throw new \Exception("The file $filePath could not be stored");
        }
        return $stored;
    }

    /**
     * Delete file.
     *
     * @param string $filePath
     * @return bool
     */
    public static function deleteFile(string $filePath): bool
    {
        $deleted = Storage::disk('gcs')->delete($filePath);

        if (!$deleted) {
            throw new \Exception("The file $filePath could not be deleted");
        }

        return $deleted;
    }
}