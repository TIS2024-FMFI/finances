@include('common.navigation', ['open_change_password' => Auth::user()->password_change_required])

<?php
//echo '<table class="usersTable">';
//echo "<div class='operations-name'>Používatelia</div>";
//
//// Table headers
//echo "<thead>";
//echo "<tr>
//    <th>ID</th>
//    <th>Email</th>
//    <th>Manipulácie</th>
//</tr>";
//echo "</thead>";
//
//// Table body
//echo "<tbody>";
//foreach ($users as $user) {
//    $user_id = $user->id;
//    $user_email = $user->email;
//
//    // Add onclick event for row
//    echo "<tr>";
//        echo "<td>{$user_id}</td>";
//        echo "<td>{$user_email}</td>";
//        echo '<td>
//
//        <div class="account_manipulations align-right">
//            <button data-id="' . $user_id . '" class="account_user_click">
//                <i class="bi bi-info-circle" title="Detail účtu"></i>
//            </button>
//        </div>
//    </td>';
//
//    echo "</tr>";
//}
//echo "</tbody>";
//echo "</table>";
//?>

<div>admin</div>


<table class="accounts_table">
    <div class='operations-name'>Všetky účty</div>
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
                        <button data-id="{$account_id}" data-title="{$account_title}" data-sap="{$account_sap_id}" class="edit_account">
                            <i class="bi bi-pencil" title="Upraviť účet"></i>
                        </button>
                        <button data-id="{$account_id}" class="delete_account">
                            <i class="bi bi-trash3" title="Zmazať účet"></i>
                        </button>
                        <button data-id="{$account_id}" class="account ">
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
