<div id="uncheck-operation-modal" class="modal-box">

    <div class="modal">
      <form id="uncheck-operation-form">
        <span class="close-modal"><i class="bi bi-x"></i></span>
        <!--<p>Naozaj si želáte označiť/odznačiť operáciu ako skontrolovanú?</p>-->
        <h2>Detail priradenej operácie</h2>
        <label for="check_operation_name"><b>Názov:</b></label>
        <p id="check_operation_name"></p>
        <div class="detail_div">
            <div>
                <label for="check_operation_main_type"><b>Kategória:</b></label>
                <p id="check_operation_main_type"></p>  
            </div>
            <div>
                <label for="check_operation_subject"><b>Subjekt:</b></label>
                <p id="check_operation_subject"></p>
            </div>
            <div>
                <label for="check_operation_sum"><b>Suma:</b></label>
                <p id="check_operation_sum"></p>
            </div>
        </div>
        <div class="detail_div">
            <div>
                <label for="check_operation_date"><b>Dátum:</b></label>
                <p id="check_operation_date"></p>
            </div>
            <div>
                <label for="check_operation_sap_id"><b>SAP ID:</b></label>
                <p id="check_operation_sap_id"></p>
            </div>
        </div>
        <div>
          <button class="proceed" type="submit" data-csrf="{{ csrf_token() }}"  id="uncheck-operation-button">Áno</button>
          <button class="cancel" type="button">Nie</button>
        </div>
      </form>
    </div>
  
  </div>