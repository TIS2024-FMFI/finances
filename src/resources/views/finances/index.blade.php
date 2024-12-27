@include('common.navigation', ['open_change_password' => Auth::user()->password_change_required])

<div class="search-container-landing">
    <input type="text" id="search-bar" placeholder="Search">
    <button id="search-button" class="button-search">🔍</button>
</div>

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
