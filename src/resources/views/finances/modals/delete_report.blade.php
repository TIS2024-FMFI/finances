<div id="delete-report-modal" class="modal-box">

  <div class="modal">
    <span class="close-modal"><i class="bi bi-x"></i></span>
    <p>Naozaj si želáte zmazať tento výkaz?</p>
      <form id="delete-report-form">
        <div>
            <button class="proceed" type="submit" data-csrf="{{ csrf_token() }}"  id="delete-report-button">Áno</button>
            <button class="cancel" type="button">Nie</button>
        </div>
      </form>
  </div>

</div>