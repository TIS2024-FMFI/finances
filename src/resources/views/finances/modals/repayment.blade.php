<div id="repay-lending-modal" class="modal-box">

  <div class="modal">
    <span class="close-modal"><i class="bi bi-x"></i></span>

    <h2>Splatenie pôžičky</h2>

    <form id="repay-lending-form"
          @if(auth()->user()->is_admin && isset($user))
              data-user-id="{{$user->id}}"
        @endif
    >

        <div class="input-box">
            <div class="field">
                <input type="date" id="repay-lending-date">
                <label for="repay-lending-date">Dátum splatenia pôžičky</label>
            </div>
            <div class="error-box" id="repay-lending-date-errors"></div>
        </div>

      <button type="submit" data-csrf="{{ csrf_token() }}"  id="repay-lending-button">Označiť pôžičku za splatenú</button>
    </form>
  </div>

</div>
