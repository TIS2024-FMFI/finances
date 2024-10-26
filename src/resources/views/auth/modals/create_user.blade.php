<div id="create-user-modal" class="modal-box">

  <div class="modal">
    <span class="close-modal"><i class="bi bi-x"></i></span>

    <h2>Nový používateľ</h2>

    <form id="create-user-form">

      <div class="input-box">
        <div class="field">
          <input type="email" id="create-user-email">
            <label for="create-user-email">E-mailová adresa</label>
        </div>
        <div class="error-box" id="create-user-email-errors"></div>
      </div>
    
      <button type="submit" data-csrf="{{ csrf_token() }}" id="create-user-button">Vytvoriť</button>
    </form>
  </div>

</div>