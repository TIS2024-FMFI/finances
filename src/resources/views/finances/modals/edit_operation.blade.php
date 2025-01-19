<div id="edit-operation-modal" class="modal-box">

  <div class="modal">
    <span class="close-modal"><i class="bi bi-x"></i></span>

    @if(auth()->user()->user_type == 2)
    <h2>Upraviť operáciu</h2>
    @else
    <h2>Pridať prílohu</h2>
    @endif

      @if(auth()->user()->user_type == 2)
      <div class="flex">
          <form id="success-operation-form">
              <button type="button" data-csrf="{{ csrf_token() }}" class="success-operation-button">Schváliť</button>

          </form>
          <form id="refuse-operation-form">
              <button type="submit" data-csrf="{{ csrf_token() }}"  class="refuse-operation-button">Zamietnuť</button>
          </form>
      </div>
      @endif

    <form id="edit-operation-form">
    @if(auth()->user()->user_type == 2)
        <div class="edit_type_category">
            <div>
                <label for="operation_edit_main_type">Kategória:</label>
                <p id="operation_edit_main_type"></p>
            </div>
            <div>
                <label for="operation_edit_type">Typ:</label>
                <p id="operation_edit_type"></p>
            </div>
        </div>

        <div class="input-box add-operation-name">
            <div class="field">
                <input
                    type="text"
                    id="edit-operation-name">
                <label for="edit-operation-name">Názov</label>
            </div>
            <div class="error-box" id="edit-operation-title-errors"></div>
        </div>

        <div class="input-box add-operation-subject">
            <div class="field">
                <input
                    type="text"
                    id="edit-operation-subject">
                <label for="edit-operation-subject">Subjekt</label>
            </div>
            <div class="error-box" id="edit-operation-subject-errors"></div>
        </div>

        <div class="input-box add-operation-sum">
            <div class="field">
                <input
                    type="text"
                    id="edit-operation-sum">
                <label for="edit-operation-sum">Suma</label>
            </div>
            <div class="error-box" id="edit-operation-sum-errors"></div>
        </div>

        <div class="input-box add-operation-to">
            <div class="field">
                <input
                    type="date"
                    id="edit-operation-to">
                <label for="edit-operation-to">Dátum</label>
            </div>
            <div class="error-box" id="edit-operation-date-errors"></div>
        </div>

        <div class="input-box add-operation-expected-date" style="display: none">
            <div class="field">
                <input
                    type="date"
                    id="edit-operation-expected-date">
                <label for="edit-operation-expected-date">Predpokladaný dátum splatenia</label>
            </div>
            <div class="error-box" id="edit-operation-expected-date-errors"></div>
        </div>
      @endif
      <div class="input-box operation-file">
        <div class="field">
        <input type="file" id="edit-operation-file" name="" accept=".doc, .docx, .pdf, .txt">
          <label for="edit-operation_file">Príloha
      <i class="bi bi-info-circle tooltip">
          <span class="tooltiptext">Nahraním novej prílohy sa pôvodná príloha zmaže. Ak chcete pridať novú prílohu a zanechať aj starú, tak si aktuálnu prílohu stiahnite a pridajte ju spoločne do priečnika s novou prílohou a následne ju dajte do ZIP a nahrajte.</span>
      </i></label>
        </div>
        <div class="error-box" id="edit-operation-attachment-errors"></div>
      </div>

      <button type="submit" data-csrf="{{ csrf_token() }}"  id="edit-operation-button">Uložiť</button>

    </form>


  </div>

</div>
