@include('common.navigation')

<?php
$from = filter_input(INPUT_GET, 'from', FILTER_SANITIZE_URL);
$to = filter_input(INPUT_GET, 'to', FILTER_SANITIZE_URL);
?>


<div class="flex-between">
    <div class="main_info">
        <a @if(auth()->user()->user_type == 4)
            href={{ route('admin_home') }}
            @else
            href={{ route('home') }}
            @endif
            class="return_home"><i class="bi bi-chevron-left"></i> Späť na prehľad
        </a>
        <div class="account-info">
            <div class="account-name">
                <p id="sap-id-detail">{{ $account->spp_symbol }}</p>
            </div>

            <div class="account-details">
                <p >Meno účtu: {{ $account->spp_symbol }}</p>
                <p >Správca: {{ $account->getSpravca() }}</p>
            </div>
        </div>

    </div>
</div>

<table class="usersTable">
    <div class="import-sap-operations-div">
        <div class='operations-name'>Používatelia účtu</div>
        <button class="button-filter" type="button" data-account-id="{{ $account->id }}" data-date-errors="{{$errors->first('to')}}" id="add-user-check">Pridať použivateľa</button>
    </div>

<?php
    // Table headers should be inside <thead>
    echo "<thead>";
    echo "<tr>
            <th style='width: 10%;'>ID</th>
            <th style='width: 50%;'>Email</th>
            <th style='width: 15%;' class='align-right'>Zmazať používateľa</th>
            <th style='width: 25%;' class='align-right'>Zostatok</th>
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
    $zostatkoveEndDate = Date::create($currentYear, 1, 1);



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
        echo "
        <td class='align-right'>
            <button class='remove-user-button'
                    data-user-id=" . $user_id . "
                    data-account-id=" . $account->id . ">
                <i class='bi bi-trash3' title='Zmazať používateľa'></i>
            </button>
        </td>";

        // Display balance in green if positive, red if negative
        if ($user_balance >= 0) {
            echo "<td class=\"align-right\" style=\"color: green;\">" . number_format($user_balance, 2, ',', ' ') . "€</td>";
        } else {
            echo "<td class=\"align-right\" style=\"color: red;\">" . number_format($user_balance, 2, ',', ' ') . "€</td>";
        }
        echo "</tr>";
    }


    $zostatkove_out = max(0, $sum_zostatkove - $sum_expenses);

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
            <p id="account-balance"><em>{{ number_format($zostatkove_out, 2, ',', ' ') }}€</em></p>
        </div>

    </div>
</div>

<div class="search-container">
    <form method="GET" action="{{ url()->current() }}" class="search-container-form">
        <input
            type="text"
            name="search"
            placeholder="Hľadať podľa názvu"
            value="{{ request('search') }}"
            id=searchh
        >
        <input type="hidden" name="from" value="{{ request('from') }}">
        <input type="hidden" name="to" value="{{ request('to') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="hidden" name="operation_type" value="{{ request('operation_type') }}">

        <button type="submit" class="button-search">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#732726FF" class="search-svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>

        </button>

    </form>
</div>

<div class="filter-box">
    <div>
        <label>Od:</label>
        <input type="date" id="filter-operations-from" value="<?php echo $from ?>"></input>

        <label>Do:</label>
        <input type="date" id="filter-operations-to" value="<?php echo $to ?>"></input>

        <label class="status-label">Status:</label>
        <select id="filter-status">
            <option value="">---</option>
            <option value="0" {{ $status === '0' ? 'selected' : '' }}>Čaká sa</option>
            <option value="1" {{ $status === '1' ? 'selected' : '' }}>Schválené</option>
            <option value="2" {{ $status === '2' ? 'selected' : '' }}>Zamietnuté</option>
        </select>
        <span>&nbsp;&nbsp;</span>
        <label for="operation-type-label">Typ:</label>
        <span>&nbsp;</span>
        <select id="filter-operation-type">
            <option value="" {{ $operation_type == '' ? 'selected' : '' }}>---</option>
            <option value="1" {{ $operation_type == '1' ? 'selected' : '' }}>Služba na faktúru</option>
            <option value="2" {{ $operation_type == '2' ? 'selected' : '' }}>Grant</option>
            <option value="3,10" {{ $operation_type == '3,10' ? 'selected' : '' }}>Pôžička</option>
            <option value="4,11" {{ $operation_type == '4,11' ? 'selected' : '' }}>Splatenie pôžičky</option>
            <option value="5,12" {{ $operation_type == '5,12' ? 'selected' : '' }}>Iný</option>
            <option value="6" {{ $operation_type == '6' ? 'selected' : '' }}>Nákup na faktúru</option>
            <option value="7" {{ $operation_type == '7' ? 'selected' : '' }}>Nákup cez Marquet</option>
            <option value="8" {{ $operation_type == '8' ? 'selected' : '' }}>Drobný nákup</option>
            <option value="9" {{ $operation_type == '9' ? 'selected' : '' }}>Pracovná cesta</option>
        </select>
        <span>&nbsp;&nbsp;</span>
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
    <tr> <th>Používateľ</th>
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

        @if( $operation->isChecked() )
        <td style="background-color: lightgreen;">Áno</td>
        @else
        <td style="background-color: red;">Nie</td>
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

<div class="operations-name">
        SAP operácie
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
                            <button type="button" data-sap-operation-id="{{ $operation->id }}" class="sap-operation-uncheck" style="color: green;"><i  class="bi bi-check2-all" title="Odznačiť operáciu"></i>
                @endif
            </td>
        </tr>

    @endforeach
</table>



@include('common.footer')
