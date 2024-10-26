<div id="create-account-modal" class="modal-box">

  <div class="modal">
    <span class="close-modal"><i class="bi bi-x"></i></span>

    <h2>Pridať účet</h2>

    <form id="create-account-form"
          @if(auth()->user()->is_admin && isset($user))
              data-user-id="{{$user->id}}"
        @endif
    >
      <div class="input-box">
        <div class="field">
          <input type="text" id="add-account-name">
          <label for="add-account-name">Názov</label>
        </div>
        <div class="error-box" id="add-account-name-errors"></div>
      </div>

      <div class="input-box">
        <div class="field">
          <input type="text" id="add-account-sap-id" placeholder="O-01-234/567-90">
          <label for="add-account-sap-id">SAP ID</label>
        </div>
        <div class="error-box" id="add-account-sap-id-errors"></div>
      </div>

      <button type="submit" data-csrf="{{ csrf_token() }}"  id="create-account-button">Uložiť</button>
    </form>
  </div>

</div>
