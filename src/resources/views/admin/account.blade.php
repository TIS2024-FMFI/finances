@include('common.navigation')

<?php
$from = filter_input(INPUT_GET, 'from', FILTER_SANITIZE_URL);
$to = filter_input(INPUT_GET, 'to', FILTER_SANITIZE_URL);
?>

<div class="flex-between">
    <div class="main_info">
        <a @if(auth()->user()->is_admin)
               href={{ route('admin_home') }}
           @else
            href={{ route('home') }}
           @endif
            class="return_home"><i class="bi bi-chevron-left"></i> Späť na prehľad
        </a>
        <h1>{{ $account_title }}</h1>
        <label for="sap-id-detail"><b>SAP ID:</b></label>
        <p id="sap-id-detail">{{ $account->sap_id }}</p>
    </div>
    <div class="switch-box">
        <p>Výpis účtu</p>
        <label class="switch">
            <input data-account-id="{{ $account->id }}" data-fake-admin-id="{{ null }}" class="toggle-button" type="checkbox">
            <span class="slider round"></span>
        </label>
        <p>SAP</p>
    </div>
</div>
<?php

echo '<table class = "usersTable" >';
echo "<h1>Používatelia účtu</h1>";

echo "<tr>";
echo <<<EOL
            <td>ID</td>
            <td>Email</td>
            <td>Zostatok</td>
            EOL;
echo "</tr>";
foreach ($users as $user) {
    $user_id = $user->id;
    $user_email = $user->email;
    $incomes = $account->userOperationsBetween($user, Illuminate\Support\Facades\Date::minValue(), Illuminate\Support\Facades\Date::maxValue())->incomes()->sum('sum');
    $expenses = $account->userOperationsBetween($user, Illuminate\Support\Facades\Date::minValue(), Illuminate\Support\Facades\Date::maxValue())->expenses()->sum('sum');
    $user_balance = $incomes - $expenses;

    echo "<tr>";
    echo "
            <td>{$user_id}</td>
            <td>{$user_email}</td>";
    if( $user_balance >= 0){
        echo "<td class=\"align-right\" style=\"color: green;\">" . number_format($user_balance, 2, ',', ' ') . "€</td>";
    }
    else{
        echo "<td class=\"align-right\" style=\"color: red;\">-" . number_format($user_balance, 2, ',', ' ') . "€</td>";
    }

    echo "</tr>";
}
echo "</table>";

echo "<h1>Operácie</h1>";

?>


<div class="filter-box">

    <div>

        <label>Od:</label><input type="date" id="filter-operations-from" value="<?php echo $from ?>"></input>
        <label>Do:</label><input type="date" id="filter-operations-to" value="<?php echo $to ?>"></input>
        <button type="button" data-account-id="{{ $account->id }}" data-date-errors="{{$errors->first('to')}}" id="filter-operations">Filtrovať</button>
        <button data-account-id="{{ $account->id }}" type="button" id="operations-export">Exportovať</button>
    </div>

    <div>
        <!-- <button data-account-id="{{ $account->id }}" data-csrf="{{ csrf_token() }}" id="create_operation" type="button" title="Nová operácia">+</button> -->
    </div>
</div>

@if ($errors->has('to'))
    <div class="error-div" style="width: 70%; margin: 0px 0px 0px 50px">
        <p style="color:red">{{ $errors->first('to') }}</p>
    </div>
@endif

<table>
    <tr>
        <th>Poradie</th>
        <th>Používatel</th>
        <th>Názov</th>
        <th>Dátum</th>
        <th>Typ</th>
        <th class="w-100">Skontrolované</th>
        <th class="align-right">Suma</th>
        <th></th>
    </tr>

    @foreach ($operations as $key=>$operation)

        <tr>
            <td>{{ ($operations->currentPage() - 1) * $operations->perPage() + $key + 1}}.</td>
            <td>{{ $operation->user()->email }}</td>
            <td>{{ $operation->title }}</td>
            <td>{{ $operation->date->format('d.m.Y') }}</td>
            <td>{{ $operation->operationType->name }}</td>
            @if( $operation->isLending() )
                <td>-</td>
            @elseif( $operation->isChecked() )
                <td>Áno</td>
            @else
                <td>Nie</td>
            @endif
            @if( $operation->isExpense())
                <td class="align-right" style="color: red;">-{{ $operation->sum }}€</td>
            @else
                <td class="align-right" style="color: green;">{{ $operation->sum }}€</td>
            @endif
            <td>
                <button type="button" data-operation-id="{{ $operation->id }}" class="operation-detail"><i  class="bi bi-info-circle" title="Detail operácie"></i></button>
                @if( $operation->isRepayment() )
                    <button type="button" data-operation-id="{{ $operation->id }}" class="operation-delete"><i class="bi bi-trash3" title="Zmazať operáciu"></i>
                        @elseif ( $operation->isLending() )
                            <button type="button" data-operation-id="{{ $operation->id }}" data-csrf="{{ csrf_token() }}" class="operation-edit"><i class="bi bi-pencil" title="Upraviť operáciu"></i>
                                @if (! $operation->lending->repayment)
                                    <button type="button" data-operation-id="{{ $operation->id }}" data-csrf="{{ csrf_token() }}" class="operation-repayment"><i class="bi bi-cash-coin" title="Splatiť pôžičku"></i>
                                        @endif
                                        <button type="button" data-operation-id="{{ $operation->id }}" class="operation-delete"><i class="bi bi-trash3" title="Zmazať operáciu"></i>
                                            @else
                                                <button type="button" data-operation-id="{{ $operation->id }}" data-csrf="{{ csrf_token() }}" class="operation-edit"><i class="bi bi-pencil" title="Upraviť operáciu"></i>
                                                    @if(! $operation->isChecked())
                                                        <button type="button" data-operation-id="{{ $operation->id }}" class="financial-operation-check"><i  class="bi bi-check2-all" title="Označiť operáciu"></i>
                                                            @else
                                                                <button type="button" data-operation-id="{{ $operation->id }}" class="financial-operation-uncheck"><i  class="bi bi-check2-all" title="Odznačiť operáciu"></i>
                                                                    @endif
                                                                    <button type="button" data-operation-id="{{ $operation->id }}" class="operation-delete"><i class="bi bi-trash3" title="Zmazať operáciu"></i>
                @endif
            </td>
        </tr>

    @endforeach
</table>

<div class="table-sum">
    <div class="pagination"> {{ $operations->links("pagination::semantic-ui") }} </div>

    <p id="income">Príjmy: <em>{{ $incomes_total }}€</em></p>
    <p id="outcome">Výdavky: <em>{{ $expenses_total }}€</em></p>
    @if( ($incomes_total - $expenses_total) >= 0)
        <p id="total">Rozdiel: <em style="color: green;">{{ $incomes_total - $expenses_total }}€</em></p>
    @else
        <p id="total">Rozdiel: <em style="color: red;">{{ $incomes_total - $expenses_total }}€</em></p>
    @endif
    <p id="account-balance">Celkový zostatok na účte: <em>{{ $account_balance }}€</em></p>
</div>

<table>
    <tr>
        <th>Poradie</th>
        <th>Názov</th>
        <th>Dátum</th>
        <th>Typ</th>
        <th class="w-100">Skontrolované</th>
        <th class="align-right">Suma</th>
        <th></th>
    </tr>

    @foreach ($sapOperations as $key=>$operation)

        <tr
            @if($operation->isChecked())
                style="background-color: lightgreen;"
            @endif
        >
            <td>{{ ($operations->currentPage() - 1) * $operations->perPage() + $key + 1}}.</td>
            <td>{{ $operation->title }}</td>
            <td>{{ $operation->date->format('d.m.Y') }}</td>
            <td>{{ $operation->operationType->name }}</td>
            @if(! is_null($operation->financialOperation) )
                <td>Áno</td>
            @else
                <td>Nie</td>
            @endif
            @if( $operation->isExpense())
                <td class="align-right" style="color: red;">-{{ $operation->sum }}€</td>
            @else
                <td class="align-right" style="color: green;">{{ $operation->sum }}€</td>
            @endif
            <td>
                @if(! $operation->isChecked())
                    <button type="button" data-sap-operation-id="{{ $operation->id }}" class="sap-operation-check"><i  class="bi bi-check2-all" title="Označiť operáciu"></i>
                        @else
                            <button type="button" data-sap-operation-id="{{ $operation->id }}" class="sap-operation-uncheck"><i  class="bi bi-check2-all" title="Odznačiť operáciu"></i>
                @endif
            </td>
        </tr>

    @endforeach
</table>


@include('common.footer')
