<?php

namespace App\Http\Controllers\FinancialOperations;

use App\Exceptions\StorageException;
use App\Http\Controllers\Controller;
use App\Http\Helpers\FileHelper;
use App\Models\FinancialOperation;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Manages the functionality of the 'operation detail' modal.
 */
class OperationDetailController extends Controller
{
    /**
     * Gets the model of a single operation.
     *
     * @param FinancialOperation $operation
     * the operation whose data are requested
     * @return array
     * an array containing the operation's model
     */
    public function getData(FinancialOperation $operation)
    {
        return ['operation' => $operation];
    }

    /**
     * Downloads the attachment file of a financial operation.
     *
     * @param FinancialOperation $operation
     * the operation whose attachment should be downloaded
     * @return StreamedResponse
     * output stream containing the attachment file
     * @throws StorageException
     */
    public function downloadAttachment(FinancialOperation $operation)
    {
        $path = $operation->attachment;
        $fileName = $operation->generateAttachmentFileName();

        return FileHelper::downloadFileIfExists($path, $fileName);
    }

}
