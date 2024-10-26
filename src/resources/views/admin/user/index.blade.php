@include('common.navigation', ['open_change_password' => Auth::user()->password_change_required])

<h1>Moje účty</h1>
<div class="accounts_box">

    <?php
    $user_id  = $user->id;
    foreach ($accounts as $account) {
        $account_balance = $account->getBalance();
        $account_id = $account->id;
        $account_sap_id = $account->sap_id;
        $account_title = $account->user->first()?->pivot?->account_title ?? 'Pomenuj ma';
        $color_of_balance = 'red';
        if($account_balance >= 0){
            $color_of_balance = 'green';
        }
        echo <<<EOL
                <div class="account_box">
                    <div data-id="$account_id" data-user_id="$user_id" class="account_admin">
                        <p>$account_sap_id</p>
                        <p>Zostatok na účte: <em style="color: $color_of_balance";>$account_balance €</em></p>
                    </div>

                </div>
                EOL;
    }
    ?>

    <div class="add_account_button">
        <i class="bi bi-plus" title="Pridať účet"></i>
    </div>
</div>

@include('common.footer')
