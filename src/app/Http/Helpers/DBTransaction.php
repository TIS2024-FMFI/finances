<?php

namespace App\Http\Helpers;
use Illuminate\Support\Facades\DB;
use \Exception;

/**
 * A helper class to represent a database transaction.
 * 
 * This class provides methods to:
 *      - execute a constructed transaction
 */
class DBTransaction
{
    /**
     * The block to execute within a database transaction.
     * 
     * @var callable
     */
    private $transactionBlock;

    /**
     * The block to execute when an exception is raised.
     * 
     * @var callable
     */
    private $exceptionBlock;

    /**
     * Construct a new database transaction object.
     * 
     * @param callable $transactionBlock
     * the block to execute within a database transaction 
     * @param callable $exceptionBlock
     * the block to execute when an exception is raised
     */
    public function __construct(callable $transactionBlock, callable $exceptionBlock = null)
    {
        $this->transactionBlock = $transactionBlock;
        $this->exceptionBlock = $exceptionBlock;
    }

    /**
     * Run the database transaction.
     * 
     * @throws \Exception
     * a rethrown exception raised within the transaction block
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        try {
            call_user_func($this->transactionBlock);
        } catch (Exception $e) {
            DB::rollBack();

            if ($this->exceptionBlock !== null) {
                call_user_func($this->exceptionBlock);
            }

            throw $e;
        }

        DB::commit();
    }
}
