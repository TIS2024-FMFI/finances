@include('common.navigation')

<?php
$from = filter_input(INPUT_GET, 'from', FILTER_SANITIZE_URL);
$to = filter_input(INPUT_GET, 'to', FILTER_SANITIZE_URL);


$account_balance = $account->getBalance();

$thisUser = auth()->user();

$sum_incomes = 0;
$sum_expenses = 0;
$sum_rozdiel = 0;


//not working yet

//foreach ($users as $user) {
//    $user_id = $user->id;
//    $user_email = $user->email;
//    $incomes = $account->userOperationsBetween($user, Illuminate\Support\Facades\Date::minValue(), Illuminate\Support\Facades\Date::maxValue())->incomes()->sum('sum');
//    $expenses = $account->userOperationsBetween($user, Illuminate\Support\Facades\Date::minValue(), Illuminate\Support\Facades\Date::maxValue())->expenses()->sum('sum');
//    $user_balance = $incomes - $expenses;
//
//    $sum_incomes += $incomes;
//    $sum_expenses += $expenses;
//    $sum_rozdiel += $user_balance;
//
//}



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

<div class="info-box">
    <div class="operations-name">
        Operácie
    </div>

    <div class="table-sum">

        <div class="table-sum-row">
            <p>Moje Príjmy: </p>
            <p id="income"><em>{{ $incomes_total }}€</em></p>
        </div>

        <div class="table-sum-row">
            <p>Moje Výdavky:</p>
            <p id="outcome"><em>{{ $expenses_total }}€</em></p>
        </div>

        <div class="table-sum-row">
            <p>Všetky Príjmy: </p>
            <p id="income"><em>{{ $sum_incomes }}€</em></p>
        </div>

        <div class="table-sum-row">
            <p>Všetky Výdavky:</p>
            <p id="outcome"><em>{{ $sum_expenses }}€</em></p>
        </div>

        <div class="table-sum-row">
            @if( ($incomes_total - $expenses_total) >= 0)
            <p>Celkový zostatok na účte:</p>
            <p id="total"><em style="color: green;">{{ $account_balance }}€</em></p>
            @else
            <p>Celkový zostatok na účte:</p>
            <p id="total"><em style="color: red;">{{ $account_balance}}€</em></p>
            @endif

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
<!--            <th>Poradie</th>-->
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
            <button type="button" data-operation-id="{{ $operation->id }}" class="operation-delete">
                <i class="bi bi-trash3" title="Zmazať operáciu"></i>
                @endif
        </td>
    </tr>

    @endforeach
</table>

<div class="pagination"> {{ $operations->links("pagination::semantic-ui") }} </div>







@include('common.footer')
