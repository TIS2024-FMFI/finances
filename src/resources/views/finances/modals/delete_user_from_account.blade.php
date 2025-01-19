<div id="delete-user-from-account-modal" class="modal-box">

  <div class="modal">
    <span class="close-modal"><i class="bi bi-x"></i></span>
    <p>Naozaj si želáte vymazať používateľa z účtu?</p>
      <form id="delete-user-from-account-form">
        <div>
            <button class="proceed" type="submit" data-csrf="{{ csrf_token() }}"  id="delete-user-from-account-button">Áno</button>
            <button class="cancel" type="button">Nie</button>
        </div>
      </form>
  </div>

</div>