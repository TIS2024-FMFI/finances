@include('common.navigation')

<?php
$from = filter_input(INPUT_GET, 'from', FILTER_SANITIZE_URL);
$to = filter_input(INPUT_GET, 'to', FILTER_SANITIZE_URL);
?>


<div class="flex-between">
    <div class="main_info">
        <a @if(auth()->user()->user_type == 2)
            href={{ route('admin_home') }}
            @else
            href={{ route('home') }}
            @endif
            class="return_home"><i class="bi bi-chevron-left"></i> Späť na prehľad
        </a>
        <div class="account-info">
            <div class="account-name">
                <p id="sap-id-detail">{{ $account->sap_id }}</p>
            </div>

            <div class="account-details">
                <p >Meno účtu: {{ $account->name }}</p>
                <p >Správca: {{ $account->getSpravca() }}</p>
            </div>
        </div>

    </div>


</div>


<?php

    echo '<table class="usersTable">';
    echo "<div class='operations-name'>Používatelia účtu</div>";

    // Table headers should be inside <thead>
    echo "<thead>";
    echo "<tr>
        <th>ID</th>
        <th>Email</th>
        <th>Zostatok</th>
      </tr>";
    echo "</thead>";

    // Table body should be inside <tbody>
    echo "<tbody>";

    $account_balance = $account->getBalance();
    $sum_incomes = 0;
    $sum_expenses = 0;
    $sum_rozdiel = 0;
    $sum_zostatkove = 0;
    $currentYear = Date::now()->year;
    $zostatkoveStartDate = Date::minValue();
    $zostatkoveEndDate = Date::create($currentYear-1, 1, 1);


    foreach ($users as $user) {
        $user_id = $user->id;
        $user_email = $user->email;
        $incomes = $account->userOperationsBetween($user, Illuminate\Support\Facades\Date::minValue(), Illuminate\Support\Facades\Date::maxValue())->incomes()->sum('sum');
        $expenses = $account->userOperationsBetween($user, Illuminate\Support\Facades\Date::minValue(), Illuminate\Support\Facades\Date::maxValue())->expenses()->sum('sum');
        $user_balance = $incomes - $expenses;

        $zostatkopveIncomes = $account->userOperationsBetween($user, $zostatkoveStartDate, $zostatkoveEndDate)->incomes()->sum('sum');

        $sum_incomes += $incomes;
        $sum_expenses += $expenses;
        $sum_rozdiel += $user_balance;
        $sum_zostatkove += $zostatkopveIncomes;

        echo "<tr>";
        echo "
            <td>{$user_id}</td>
            <td>{$user_email}</td>";

        // Display balance in green if positive, red if negative
        if ($user_balance >= 0) {
            echo "<td class=\"align-right\" style=\"color: green;\">" . number_format($user_balance, 2, ',', ' ') . "€</td>";
        } else {
            echo "<td class=\"align-right\" style=\"color: red;\">" . number_format($user_balance, 2, ',', ' ') . "€</td>";
        }

        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";

?>

<div class="info-box">
    <div class="operations-name">
        Operácie
    </div>

    <div class="table-sum">

        <div class="table-sum-row">
            <p>Príjmy: </p>
            <p id="income"><em>{{ number_format($sum_incomes, 2, ',', ' ') }}€</em></p>
        </div>

        <div class="table-sum-row">
            <p>Výdavky:</p>
            <p id="outcome"><em>{{  number_format($sum_expenses, 2, ',', ' ')}}€</em></p>
        </div>

        <div class="table-sum-row">
            @if( ($sum_rozdiel) >= 0)
            <p>Celkový zostatok na účte:</p>
            <p id="total"><em style="color: green;">{{  number_format($sum_rozdiel, 2, ',', ' ')  }}€</em></p>
            @else
            <p>Rozdiel:</p>
            <p id="total"><em style="color: red;">{{ number_format($sum_rozdiel, 2, ',', ' ') }}€</em></p>
            @endif

        </div>

        <div class="table-sum-row">
            <p>Zostatkove(< {{$currentYear}}):</p>
            <p id="account-balance"><em>{{ number_format($sum_zostatkove - $sum_expenses, 2, ',', ' ') }}€</em></p>
        </div>

        <div class="switch-box">
            <p>Výpis účtu</p>
            <label class="switch">
                <input data-account-id="{{ $account->id }}" class="toggle-button" type="checkbox">
                <span class="slider round"></span>
            </label>
            <p>SAP</p>
        </div>



    </div>
</div>


<div class="filter-box">
    <div>
        <label>Od:</label>
        <input type="date" id="filter-operations-from" value="<?php echo $from ?>"></input>

        <label>Do:</label>
        <input type="date" id="filter-operations-to" value="<?php echo $to ?>"></input>

        <button class="button-filter" type="button" data-account-id="{{ $account->id }}" data-date-errors="{{$errors->first('to')}}" id="filter-operations">Filtrovať</button>
        <button class="button-filter" data-account-id="{{ $account->id }}" type="button" id="operations-export">Exportovať</button>
    </div>

    <div>
        <button class="button-add" data-account-id="{{ $account->id }}" data-csrf="{{ csrf_token() }}" id="create_operation" type="button" title="Nová operácia">+</i></button>
    </div>

</div>

@if ($errors->has('to'))
    <div class="error-div" style="width: 70%; margin: 0px 0px 0px 50px">
        <p style="color:red">{{ $errors->first('to') }}</p>
    </div>
@endif

<table>
    <tr>
        <th>Používateľ</th>
        <th>Názov</th>
        <th>Dátum</th>
        <th>Typ</th>
        <th class="w-100">Spárované</th>
        <th class="w-100">Status</th>
        <th class="align-right">Suma</th>
        <th class="align-right">Manipulácie</th>
    </tr>

    @foreach ($operations as $key=>$operation)

    <tr>
        <!--            <td>{{ ($operations->currentPage() - 1) * $operations->perPage() + $key + 1}}.</td>-->
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

        <td>{{ $operation->stringStatus() }}</td>

        @if( $operation->isExpense())
        <td class="align-right" style="color: red;">-{{ $operation->sum }}€</td>
        @else
        <td class="align-right" style="color: green;">{{ $operation->sum }}€</td>
        @endif
        <td>
            <button type="button" data-operation-id="{{ $operation->id }}" class="operation-detail">
                <i  class="bi bi-info-circle" title="Detail operácie"></i>
            </button>
            @if( $operation->isRepayment() )
            <button type="button" data-operation-id="{{ $operation->id }}" class="operation-delete">
                <i class="bi bi-trash3" title="Zmazať operáciu"></i>
                @elseif ( $operation->isLending() )
                <button type="button" data-operation-id="{{ $operation->id }}" data-csrf="{{ csrf_token() }}" class="operation-edit">
                    <i class="bi bi-pencil" title="Upraviť operáciu"></i>
                    @if (! $operation->lending->repayment)
                    <button type="button" data-operation-id="{{ $operation->id }}" data-csrf="{{ csrf_token() }}" class="operation-repayment">
                        <i class="bi bi-cash-coin" title="Splatiť pôžičku"></i>
                        @endif
                        <button type="button" data-operation-id="{{ $operation->id }}" class="operation-delete">
                            <i class="bi bi-trash3" title="Zmazať operáciu"></i>
                            @else
                            <button type="button" data-operation-id="{{ $operation->id }}" data-csrf="{{ csrf_token() }}" class="operation-edit">
                                <i class="bi bi-pencil" title="Upraviť operáciu"></i>
                                @if(! $operation->isChecked())
                                <button type="button" data-operation-id="{{ $operation->id }}" class="financial-operation-check">
                                    <i  class="bi bi-check2-all" title="Označiť operáciu"></i>
                                    @else
                                    <button type="button" data-operation-id="{{ $operation->id }}" class="financial-operation-uncheck">
                                        <i  class="bi bi-check2-all" title="Odznačiť operáciu"></i>
                                        @endif
                                        <button type="button" data-operation-id="{{ $operation->id }}" class="operation-delete">
                                            <i class="bi bi-trash3" title="Zmazať operáciu"></i>
                                            @endif
        </td>
    </tr>

    @endforeach
</table>

<div class="pagination"> {{ $operations->links("pagination::semantic-ui") }} </div>

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
