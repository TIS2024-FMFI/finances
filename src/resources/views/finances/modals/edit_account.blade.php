<div id="edit-account-modal" class="modal-box">

  <div class="modal">
    <span class="close-modal"><i class="bi bi-x"></i></span>

    <h2>Upraviť účet</h2>
    <form id="edit-account-form">
        <div class="input-box">
          <div class="field">
            <input type="text" id="edit-account-name">
            <label for="edit-account-name">Názov</label>
          </div>
          <div class="error-box" id="edit-account-name-errors"></div>
        </div>

        <div class="input-box">
          <div class="field">
            <input type="text" id="edit-account-sap-id" placeholder="O-01-234/567-90">
            <label for="edit-account-sap-id">SAP ID</label>
          </div>
          <div class="error-box" id="edit-account-sap-id-errors"></div>
        </div>

        <button type="submit" data-csrf="{{ csrf_token() }}"  id="edit-account-button">Uložiť</button>
    </form>

  </div>

</div>