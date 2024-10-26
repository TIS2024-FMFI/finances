<div id="check-operation-modal" class="modal-box">

  <div class="modal">
    <form id="check-operation-form">
      <span class="close-modal"><i class="bi bi-x"></i></span>
      <!--<p>Naozaj si želáte označiť/odznačiť operáciu ako skontrolovanú?</p>-->
      <div class="input-box choose-lending" style="display:none">
        <div class="field">
          <select id="check-operation-choice" name="operation-choice">

          </select>
          <label for="check-operation-choice">Operácia na oznančenie</label>
        </div>
        <div class="error-box" id="lending-choice-errors"></div>
      </div>
      <div>
        <button class="proceed" type="submit" data-csrf="{{ csrf_token() }}"  id="check-operation-button">Áno</button>
        <button class="cancel" type="button">Nie</button>
      </div>
    </form>
  </div>

</div>