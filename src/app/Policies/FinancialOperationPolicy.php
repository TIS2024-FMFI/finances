<?php

    namespace App\Policies;

    use App\Models\Account;
    use App\Models\FinancialOperation;
    use App\Models\Lending;
    use App\Models\User;
    use Illuminate\Auth\Access\HandlesAuthorization;

    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\DB;

    class FinancialOperationPolicy
    {
        use HandlesAuthorization;

        /**
         * Determine whether a user can access a financial operation.
         *
         * This method checks if the given user has the authorization to view a specific financial operation.
         * Admin users are granted universal access to all operations, while non-admin users must be the owner
         * of the operation to access it.
         *
         * @param \App\Models\User $user The user whose authorization request is being evaluated.
         * @param \App\Models\FinancialOperation $financialOperation The financial operation the user is attempting to access.
         * @return bool Returns true if the user is authorized to view the financial operation, false otherwise.
         */
        public function view(User $user, FinancialOperation $financialOperation)
        {
            // Grant access to admin users for any financial operation.
            if ($user->is_admin) {
                return true;
            }

            // For non-admin users, check if the user is the owner of the financial operation.
            // The ownership is determined by comparing the user's ID with the user_id attribute of the financial operation.
            return $user->id === $financialOperation->user_id;
        }



        /**
         * Determine whether a user can create a financial operation.
         *
         * @param  \App\Models\User  $user
         * the user whose request to authorize
         * @param  \App\Models\Account  $account
         * the account under which the user is attempting to create the operation
         * @return bool
         * true if the user is allowed to perform this operation, false otherwise
         */
        public function create(User $user, Account $account)
        {
            // Grant access to admin users for any financial operation.
            if ($user->is_admin) {
                return true;
            }
            return $user->accounts->contains($account);
        }

        /**
         * Determine whether a user can create a repayment operation.
         *
         * @param  \App\Models\User  $user
         * the user whose request to authorize
         * @param  \App\Models\Lending  $lending
         * the lending for which the user is attempting to create the repayment
         * @return bool
         * true if the user is allowed to perform this operation, false otherwise
         */
        public function createRepayment(User $user, Lending $lending)
        {// Grant access to admin users for any financial operation.
            if ($user->is_admin) {
                return true;
            }

            return $user->id === $lending->operation->user()->id;
        }

        /**
         * Determine whether a user can update a financial operation.
         *
         * @param  \App\Models\User  $user
         * the user whose request to authorize
         * @param  \App\Models\FinancialOperation  $financialOperation
         * the financial operation the user is attempting to update
         * @return bool
         * true if the user is allowed to perform this operation, false otherwise
         */
        public function update(User $user, FinancialOperation $financialOperation)
        {
            // Grant access to admin users for any financial operation.
            if ($user->is_admin) {
                return true;
            }
            return $user->id === $financialOperation->user()->id;
        }

        /**
         * Determine whether a user can delete a financial operation.
         *
         * @param  \App\Models\User  $user
         * the user whose request to authorize
         * @param  \App\Models\FinancialOperation  $financialOperation
         * the financial operation the user is attempting to delete
         * @return bool
         * true if the user is allowed to perform this operation, false otherwise
         */
        public function delete(User $user, FinancialOperation $financialOperation)
        {
            // Grant access to admin users for any financial operation.
            if ($user->is_admin) {
                return true;
            }
            //DB::enableQueryLog();
            Log::debug('Policy for deleting financial operation data,
            Financial Op.: {data}
            finOp user: {data2}', ['data' => $financialOperation, 'data2' => $financialOperation->user()]);
            Log::debug(DB::getQueryLog());
            return $user->id === $financialOperation->user()->id;
        }
    }
