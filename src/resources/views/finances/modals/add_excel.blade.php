<div id="add-excel-modal" class="modal-box">

    <div class="modal">
        <span class="close-modal"><i class="bi bi-x"></i></span>
        <h2>Import Excel File</h2>

        <form id="excel-upload-form" enctype="multipart/form-data">
            @csrf
            <div class="input-box">
                <div class="field">
                    <input type="file" id="excel-file" name="excel_file" accept=".xlsx,.csv">
                    <label for="excel-file">Choose an Excel File</label>
                </div>
                <div class="error-box" id="excel-upload-errors"></div>
            </div>
            <button type="submit" class="upload-btn">Upload</button>
        </form>
    </div>


</div>
