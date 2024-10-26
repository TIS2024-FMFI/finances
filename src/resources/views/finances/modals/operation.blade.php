<div id="operation-modal" class="modal-box">

  <div class="modal">
    <span class="close-modal"><i class="bi bi-x"></i></span>
    <h2>Detail operácie</h2>

    <div class="detail_div">
      <div>
        <label for="operation_main_type"><b>Kategória:</b></label>
        <p id="operation_main_type"></p>  
      </div>
      <div>
        <label for="operation_type"><b>Typ:</b></label>
        <p id="operation_type"></p>
      </div>
    </div>
    
    <label for="operation_name"><b>Názov:</b></label>
    <p id="operation_name"></p>

    <div class="detail_div">
      <div>
        <label for="operation_subject"><b>Subjekt:</b></label>
        <p id="operation_subject"></p>
      </div>
      <div>
        <label for="operation_sum"><b>Suma:</b></label>
        <p id="operation_sum"></p>
      </div>
    </div>
    <div class="detail_div">
      <div>
        <label for="operation_date"><b>Dátum:</b></label>
        <p id="operation_date"></p>
      </div>
      <div>
        <label for="operation_date_until" id="operation_date_until_label" style="visibility: hidden"><b>Predpokladaný dátum splatenia:</b></label>
        <p id="operation_date_until" style="visibility: hidden"></p>
      </div>
    </div>
    <button type="button" id="previous-lending-button">Ukázať pôžičku</button>
    <button type="button" id="show-repayment-button">Ukázať operáciu splatenia</button>
    <button type="button" id="operation-attachment-button"><i class="bi bi-download" title="Stiahnuť výkaz"></i>&nbsp;Doklad</button>
  </div>

</div>
