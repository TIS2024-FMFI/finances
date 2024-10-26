@if(isset($open) && $open)
<div id="change-pass-modal" class="modal-box" style="display: flex;">
@else
<div id="change-pass-modal" class="modal-box">
@endif

  <div class="modal">

    <span class="close-modal"><i class="bi bi-x"></i></span>

    <h2>Zmena hesla</h2>

    <form id="change-pass-form">
      <div class="input-box">
        <div class="field" style="position: relative;">
          <input type="password" id="change-pass-old">
          <i class="bi bi-eye show-pass" id="show-old-pass"></i>
          <label for="change-pass-old">Aktuálne heslo</label>
        </div>
        <div class="error-box" id="change-pass-old-errors"></div>
      </div>

      <div class="input-box">
        <div class="field" style="position: relative;">
          <input type="password" id="change-pass-new1">
          <i class="bi bi-eye show-pass" id="show-new1-pass"></i>
          <label for="change-pass-new1">Nové heslo</label>
        </div>
        <div class="error-box" id="change-pass-new1-errors"></div>
      </div>

      <div class="input-box">
        <div class="field" style="position: relative;">
          <input type="password" id="change-pass-new2">
          <i class="bi bi-eye show-pass" id="show-new2-pass"></i>
          <label for="change-pass-new2">Zopakujte heslo</label>
        </div>
        <div class="error-box" id="change-pass-new2-errors"></div>
      </div>

      <button type="submit" data-csrf="{{ csrf_token() }}" id="change-pass-button">Uložiť</button>
    </form>
  </div>

</div>