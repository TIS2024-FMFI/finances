<?php

namespace App\Http\Helpers;

use App\Exceptions\StorageException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A helper class providing functions useful for managing files and file names.
 */
class FileHelper
{
    /**
     * Delete a file if it exists.
     *
     * @param string|null $path
     * the path to the file to delete
     * @throws \App\Exceptions\StorageException
     * thrown if the file could not be deleted
     */
    public static function deleteFileIfExists(string|null $path)
    {
        if ($path === null || !Storage::exists($path)) {
            return;
        }
            
        if (!Storage::delete($path)) {
            throw new StorageException('File not deleted.');
        }
    }
    
    /**
     * Download a file if it exists.
     * 
     * @param string|null $path
     * the path to the file to download
     * @param string $filename
     * the display name of the file
     * @throws \App\Exceptions\StorageException
     * thrown if the file was not found
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * a streamed response containing the file
     */
    public static function downloadFileIfExists(string|null $path, string $filename)
    {
        if ($path === null || !Storage::exists($path)) {
            throw new StorageException('File not found.');
        }

        return Storage::download($path, $filename);
    }

    /**
     * Create a sanitized version of a string, so that it can be used as a file
     * name.
     * 
     * The following rules are applied during sanitization:
     *      - each whitespace is replaced by a dash ('-')
     *      - all accented characters are translated to their closest ASCII
     *        representation
     *      - special characters (<>:"/\|,.;'!?*) are removed
     *
     * @param string $string
     * the string to be sanitized
     * @return string
     * the sanitized string
     */
    public static function sanitizeString(string $string)
    {
        $ascii = Str::ascii($string);
        $spacesSanitized = Str::replace(' ', '-', $ascii);
        
        return preg_replace('/[<>:"\/\\|,.;\'!?*]/', '', $spacesSanitized);
    }

    /**
     * Append a file's extension to a file name. If the file has no extension
     * or the path to the file is null, nothing is appended.
     *
     * @param string|null $path
     * path to the file whose extension should be extracted
     * @param string $fileName
     * the file name to which the extension should be appended
     * @return string
     * the extended file name
     */
    public static function appendFileExtension(string|null $path, string $fileName)
    {
        $extension = (empty($path)) ? '' : pathinfo($path, PATHINFO_EXTENSION);

        return (empty($extension)) ? $fileName : "$fileName.$extension";
    }
}
