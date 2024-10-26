@include('common.navigation', ['open_change_password' => Auth::user()->password_change_required])

<h1>Použivatelia</h1>

<?php
echo '<table class="usersTable">';
foreach ($users as $user) {
    $user_id = $user->id;
    $user_email = $user->email;

    // Pridanie onclick eventu na <tr> element
    echo "<tr data-id='{$user_id}' onclick='admin_user_overview(this)'>";
    echo "<td>{$user_id}</td>";
    echo "<td>{$user_email}</td>";
    echo "</tr>";
}
echo "</table>";


echo "<h1>Účty</h1>";
echo '<div class="accounts_box">';
foreach ($accounts as $account) {
    $account_balance = $account->getBalance();
    $account_id = $account->id;
    $account_sap_id = $account->sap_id;
    $color_of_balance = 'red';
    if($account_balance >= 0){
        $color_of_balance = 'green';
    }
    echo <<<EOL
                <div class="account_box">
                    <div data-id="$account_id" class="overview_account">
                        <p>$account_sap_id</p>
                        <p>Zostatok na účte: <em style="color: $color_of_balance";>$account_balance €</em></p>
                    </div>

                </div>
                EOL;
}
?>



@include('common.footer')
