@include('common.navigation', ['open_change_password' => Auth::user()->password_change_required])

<h1>Moje účty</h1>
<div class="accounts_box">

    <?php
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
                    <div data-id="$account_id" class="account">
                        <h2>$account_title</h2>
                        <p>$account_sap_id</p>
                        <p>Zostatok na účte: <em style="color: $color_of_balance";>$account_balance €</em></p>
                    </div>
                    <i data-id="$account_id" data-title="$account_title" data-sap="$account_sap_id" class="bi bi-pencil edit_account" title="Upraviť účet"></i>
                    <i data-id="$account_id" class="bi bi-trash3 delete_account" title="Zmazať účet"></i>
                </div>
                EOL;
    }
    ?>

    <div class="add_account_button">
        <i class="bi bi-plus" title="Pridať účet"></i>
    </div>
</div>

@include('common.footer')
