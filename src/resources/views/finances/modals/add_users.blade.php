<div id="add-user-modal" class="modal-box">

    <div class="modal">
      <form id="add-user-form">
        <span class="close-modal"><i class="bi bi-x"></i></span>
        <div class="input-box choose-lending" style="display:none">
          <div class="field">
            <select id="add-user-choice" name="user-choice">
  
            </select>
            <label for="add-user-choice">Použivateľ na pridanie</label>
          </div>
          <div class="error-box" id="lending-choice-errors"></div>
        </div>
        <div>
          <button class="proceed" type="submit" data-csrf="{{ csrf_token() }}"  id="add-user-button">Áno</button>
          <button class="cancel" type="button">Nie</button>
        </div>
      </form>
    </div>
  
  </div>