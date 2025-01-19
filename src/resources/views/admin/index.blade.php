@include('common.navigation', ['open_change_password' => Auth::user()->password_change_required])



<div class="search-container-landing">
    <input type="text" id="search-bar" placeholder="Search">
    <button id="search-button" class="button-search">🔍</button>
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
        <th class="align-right">Manipulácie</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($accounts as $account) {
        $account_balance = $account->getBalance();
        $account_id = $account->id;
        $account_sap_id = $account->sap_id;
        $account_name = $account->name;
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
            <tr>
                <td>{$account_sap_id}</td>
                <td>{$account_name}</td>
                <td>{$account_spravca}</td>
                <td style="color: {$color_of_balance};" class="align-right">{$account_balance}€</td>
                <td class=" ">
                    <div class="account_manipulations align-right">

                        <button data-id="{$account_id}" class="account_detail_admin ">
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchBar = document.getElementById('search-bar');
        const searchButton = document.getElementById('search-button');
        const tableRows = document.querySelectorAll('.accounts_table tbody tr');
        searchButton.addEventListener('click', function () {
            const query = searchBar.value.toLowerCase();
            tableRows.forEach(row => {
                const sapIdCell = row.querySelector('td:first-child');
                if (sapIdCell) {
                    const sapId = sapIdCell.textContent.toLowerCase();
                    if (sapId.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        });
    });
</script>

@include('common.footer')
