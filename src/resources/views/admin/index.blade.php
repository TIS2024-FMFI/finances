@include('common.navigation', ['open_change_password' => Auth::user()->password_change_required])



<div class="search-container">

    <form method="GET" class="search-container-form">
        <input type="text" id="search-bar" placeholder="Hľadať podľa SPP ID">

        <button id="search-button" class="button-search" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#732726FF" class="search-svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>

        </button>
    </form>

</div>

<table class="accounts_table">
    <div class="import-sap-operations-div">
        <div class='operations-name'>Všetky účty</div>
        <button class="button-filter" data-csrf="{{ csrf_token() }}" id="add-excel-report" type="button">Importovať SAP operácie</button>
    </div>

    <thead>
    <tr>
        <th>SAP ID</th>
        <th>Názov účtu</th>
        <th>Správca</th>
        <th class="align-right">Zostatok</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($accounts as $account) {
        $account_balance = $account->getBalance();
        $account_id = $account->id;
        $account_sap_id = $account->spp_symbol;
        $account_name = $account->spp_symbol;
        $account_spravca = $account->getSpravca();


//        $account_title = $account->user->first()?->pivot?->account_title ?? 'Pomenuj ma';
        $color_of_balance = $account_balance >= 0 ? 'green' : 'red';


//        ### Edit and delete buttons ###
//        <button data-id="{$account_id}" data-title="{$account_title}" data-sap="{$account_sap_id}" class="edit_account">
//            <i class="bi bi-pencil" title="Upraviť účet"></i>
//        </button>
//        <button data-id="{$account_id}" class="delete_account">
//            <i class="bi bi-trash3" title="Zmazať účet"></i>
//        </button>

        echo <<<EOL
            <tr data-id="{$account_id}" class="account_detail_admin">
                <td>{$account_sap_id}</td>
                <td>{$account_name}</td>
                <td>{$account_spravca}</td>
                <td style="color: {$color_of_balance};" class="align-right">{$account_balance}€</td>
            </tr>
            EOL;
    }
    ?>
    </tbody>
</table>

@include('common.footer')
