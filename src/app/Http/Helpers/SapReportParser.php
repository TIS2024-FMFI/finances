<?php

namespace App\Http\Helpers;
use App\Exceptions\FileFormatException;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * A helper class to parse a SAP report file.
 * 
 * This class provides methods to:
 *      - get the date a SAP report was exported
 */
class SapReportParser
{
    /**
     * The contents of the file to parse split into lines.
     * 
     * @var array
     */
    private array $lines;

    /**
     * Construct a new parser to parse an existing SAP report file.
     * 
     * @param string $absolutePath
     * the absolute path to the file to parse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * thrown if the provided file was not found
     */
    public function __construct(string $absolutePath)
    {
        $this->lines = File::lines($absolutePath)->all();
    }

    /**
     * Get the date the SAP report was exported.
     * 
     * @throws FileFormatException
     * thrown if the date exported was not found or had an invalid format
     * @return \Carbon\Traits\Creator
     */
    public function getDateExported()
    {
        if (empty($this->lines)) {
            throw new FileFormatException('The date exported not found.');
        }      

        $rawDate = Str::before($this->lines[0], ' ');

        if (empty($rawDate)) {
            throw new FileFormatException('The date exported not found.');
        }

        try {
            return Carbon::createFromFormat('d.m.Y', $rawDate);
        } catch (InvalidFormatException $e) {
            throw new FileFormatException('Invalid date format.');
        }
    }
}
