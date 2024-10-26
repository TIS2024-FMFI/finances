
<div id="create-operation-modal" class="modal-box">

  <div class="modal">
    <span class="close-modal"><i class="bi bi-x"></i></span>

    <h2>Pridať operáciu</h2>

    <form id="create-operation-form"
          @if(auth()->user()->is_admin && isset($user))
            data-user-id="{{$user->id}}"
        @endif

    >

      <div class="radio-buttons-box">
          <div>
            <input class="operation_type" id="income_choice" name="operation_type" type="radio" value="income" checked>&ensp;
            <label class="operation_type" for="income_choice"><i>Príjem</i></label>
          </div>
          <div>
            <input class="operation_type" id="expense_choice" name="operation_type" type="radio" value="expense">&ensp;
            <label class="operation_type" for="expense_choice"><i>Výdavok</i></label>
          </div>
          <div>
            <input class="operation_type" id="loan_choice" name="operation_type" type="radio" value="loan">&ensp;
            <label class="operation_type" for="loan_choice"><i>Splatenie pôžičky</i></label>
          </div>
      </div>

      <div class="input-box add-operation-choice">
        <div class="field">
          <select id="operation_choice" name="typ" id="add-operation-type">

          </select>
          <label for="add-operation-type">Typ operácie</label>
        </div>
        <div class="error-box" id="add-operation-type-errors"></div>
      </div>

      <div class="input-box add-operation-name">
        <div class="field">
          <input type="text" id="add-operation-name">
          <label for="add-operation-name">Názov</label>
        </div>
        <div class="error-box" id="add-operation-title-errors"></div>
      </div>

      <div class="input-box add-operation-subject">
        <div class="field">
          <input type="text" id="add-operation-subject">
          <label for="add-operation-subject">Subjekt</label>
        </div>
        <div class="error-box" id="add-operation-subject-errors"></div>
      </div>

      <div class="input-box add-operation-sum">
        <div class="field">
          <input type="text" id="add-operation-sum">
          <label for="add-operation-sum">Suma</label>
        </div>
        <div class="error-box" id="add-operation-sum-errors"></div>
      </div>

      <div class="input-box add-operation-to">
        <div class="field">
          <input type="date" id="add-operation-to">
          <label id="operation-date-label" for="add-operation-to">Dátum</label>
        </div>
        <div class="error-box" id="add-operation-date-errors"></div>
      </div>

      <div class="input-box add-operation-expected-date" style="display: none">
        <div class="field">
          <input type="date" id="add-operation-expected-date">
          <label for="add-operation-expected-date">Predpokladaný dátum splatenia</label>
        </div>
        <div class="error-box" id="add-operation-expected-date-errors"></div>
      </div>

      <div class="input-box operation-file">
        <div class="field">
          <input type="file" id="operation-file" name="" accept=".doc, .docx, .pdf, .txt">
          <label for="operation-file">Príloha</label>
        </div>
        <div class="error-box" id="add-operation-attachment-errors"></div>
      </div>

      <div class="input-box choose-lending" style="display:none">
        <div class="field">
          <select id="lending-choice" name="lending">

          </select>
          <label for="lending_choice">Pôžička na splatenie</label>
        </div>
        <div class="error-box" id="lending-choice-errors"></div>
      </div>

      <p id="lending_detail">Detail pôžičky</p>

      <div class="lending_detail_div">
        <div>
          <label for="lending_operation_name"><b>Názov:</b></label>
          <p id="lending_operation_name"></p>
        </div>
      </div>

      <div class="lending_detail_div">
        <div>
          <label for="lending_operation_subject"><b>Subjekt:</b></label>
          <p id="lending_operation_subject"></p>
        </div>
        <div>
          <label for="lending_operation_sum"><b>Suma:</b></label>
          <p id="lending_operation_sum"></p>
        </div>
      </div>

      <div class="lending_detail_div">
        <div>
          <label for="lending_operation_date"><b>Dátum:</b></label>
          <p id="lending_operation_date"></p>
        </div>
        <div>
          <label for="lending_operation_date_until" id="lending_operation_date_until_label"><b>Predpokladaný dátum splatenia:</b></label>
          <p id="lending_operation_date_until"></p>
        </div>
      </div>

      <button type="submit"  data-csrf="{{ csrf_token() }}"  id="create-operation-button">Uložiť</button>

    </form>
  </div>

</div>
