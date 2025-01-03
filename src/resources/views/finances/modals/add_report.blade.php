<div id="add-report-modal" class="modal-box">

  <div class="modal">
    <span class="close-modal"><i class="bi bi-x"></i></span>
    <h2>Pridať výkaz</h2>

    <form id="create-report-form">
      <div class="input-box">
        <div class="field">
          <input type="file" id="report-file" name="">
          <label for="report-file">SAP výkaz</label>
        </div>
        <div class="error-box" id="add-sap-report-errors"></div>
      </div>
      <button data-csrf="{{ csrf_token() }}" type="submit" class="create" id="create-report-button">Uložiť</button>
    </form>

  </div>

</div>





<div id="add-excel-modal" class="modal-box">

    <div class="modal">
        <span class="close-modal"><i class="bi bi-x"></i></span>
        <h2>Pridať Excel</h2>

        <form id="create-excel-form">
            <div class="input-box">
                <div class="field">
                    <input type="file" id="excel-file" name="excel_file">
                    <label for="excel-file">Excel súbor</label>
                </div>
                <div class="error-box" id="add-sap-excel-errors"></div>
            </div>
            <button data-csrf="{{ csrf_token() }}" type="submit" class="create" id="create-excel-button">Uložiť</button>
        </form>

    </div>

</div>
