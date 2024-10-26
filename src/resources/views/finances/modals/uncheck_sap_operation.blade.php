<div id="uncheck-sap-operation-modal" class="modal-box">

    <div class="modal">
      <form id="uncheck-sap-operation-form">
        <span class="close-modal"><i class="bi bi-x"></i></span>
        <h2>Detail priradenej operácie</h2>
        <label for="check_sap_operation_name"><b>Názov:</b></label>
        <p id="check_sap_operation_name"></p>
        <div class="detail_div">
            <div>
                <label for="check_sap_operation_main_type"><b>Kategória:</b></label>
                <p id="check_sap_operation_main_type"></p>  
            </div>
            <div>
                <label for="check_sap_operation_subject"><b>Subjekt:</b></label>
                <p id="check_sap_operation_subject"></p>
            </div>
            <div>
                <label for="check_sap_operation_sum"><b>Suma:</b></label>
                <p id="check_sap_operation_sum"></p>
            </div>
        </div>
        <div class="detail_div">
            <div>
                <label for="check_sap_operation_date"><b>Dátum:</b></label>
                <p id="check_sap_operation_date"></p>
            </div>
        </div>
        <div>
          <button class="proceed" type="submit" data-csrf="{{ csrf_token() }}"  id="uncheck-sap-operation-button">Áno</button>
          <button class="cancel" type="button">Nie</button>
        </div>
      </form>
    </div>
  
  </div>