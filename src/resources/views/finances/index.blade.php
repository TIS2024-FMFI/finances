@include('common.navigation', ['open_change_password' => Auth::user()->password_change_required])


<table class="accounts_table">
    <h1>Moje účty</h1>
    <thead>
    <tr>
        <th>SAP ID / Názov účtu</th>
        <th class="align-right">Zostatok</th>
        <th class="align-right">Manipulácie</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($accounts as $account) {
        $account_balance = $account->getBalance();
        $account_id = $account->id;
        $account_sap_id = $account->sap_id;
        $account_title = $account->user->first()?->pivot?->account_title ?? 'Pomenuj ma';
        $color_of_balance = $account_balance >= 0 ? 'green' : 'red';

        echo <<<EOL
            <tr>
                <td>{$account_sap_id}</td>
                <td style="color: {$color_of_balance};" class="align-right">{$account_balance}€</td>
                <td class=" ">
                    <div class="account_manipulations align-right">
                        <button data-id="{$account_id}" class="account_detail ">
                            <i  class="bi bi-info-circle" title="Detail účtu"></i>
                        </button>

                    </div>

                </td>
            </tr>
            EOL;
    }
    ?>
    </tbody>
</table>

@include('common.footer')
