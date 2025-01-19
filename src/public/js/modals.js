$(document).ready(function(){

    console.log()

    // Initialize Toast -->
    const Toast = Swal.mixin({
        toast: true,
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    })
    // <-- Initialize Toast


    function handleOperationFormSubmission(formSelector, urlSuffix) {
        $(formSelector).find("button").on("click", function (e) {
            e.preventDefault();
            let csrf = $("#create-excel-button").data("csrf");
            let operation_id = $("#edit-operation-form").data("operation-id");
            let fileData = new FormData();

            fileData.append('_token', csrf);
            fileData.append('_method', 'PATCH');
            fileData.append('title', "1");

            console.log(`${root}/operations/${operation_id}/${urlSuffix}`)

            $.ajax({
                url: `${root}/operations/${operation_id}/${urlSuffix}`,
                type: "POST",
                contentType: false,
                processData: false,
                dataType: "json",
                data: fileData,
            })
                .done(function (response) {
                    Toast.fire({
                        icon: 'success',
                        title: response.displayMessage,
                    });
                    location.reload();
                    $(".modal-box").css("display", "none");
                    $.fn.editOperationClearForm(true);
                })
                .fail(function (response) {
                    console.log(response);
                });
        });
    }



    handleOperationFormSubmission("#success-operation-form", "status-accept");
    handleOperationFormSubmission("#refuse-operation-form", "status-refuse");

})
