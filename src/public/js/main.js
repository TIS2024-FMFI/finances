$(document).ready(function(){

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

    // Show password icons -->
    $('#show-login-pass').on('mousedown mouseup', e => {
        if (e.type == "mousedown") {
            $('#login-pass').attr('type','text');
        } else {
            $('#login-pass').attr('type','password');
        }
    });

    $('#show-old-pass').on('mousedown mouseup', e => {
        if (e.type == "mousedown") {
            $('#change-pass-old').attr('type','text');
        } else {
            $('#change-pass-old').attr('type','password');
        }
    });

    $('#show-new1-pass').on('mousedown mouseup', e => {
        if (e.type == "mousedown") {
            $('#change-pass-new1').attr('type','text');
        } else {
            $('#change-pass-new1').attr('type','password');
        }
    });

    $('#show-new2-pass').on('mousedown mouseup', e => {
        if (e.type == "mousedown") {
            $('#change-pass-new2').attr('type','text');
        } else {
            $('#change-pass-new2').attr('type','password');
        }
    });
    // <-- Show password icons

    // Authorization forms -->
    // Create first user form -->
    $("#first-user-form").on("submit", function(e) {
        e.preventDefault();

        let email = $("#first-user-email").val();
        let csrf = $("#first-user-button").data("csrf");

        $.ajax({
            url: root + "/register",
            type: "POST",
            data: {
                "_token": csrf,
                "email": email
            }
        }).done(function(response) {
            window.location.href = root + '/login';
        }).fail(function(response) {
            $.fn.createFirstUserClearForm();
            console.log(response);
            if (typeof response.responseJSON != 'undefined') {
                if (response.status === 422) {
                    let errors = response.responseJSON.errors;

                    if (typeof errors.email != 'undefined') {
                        $("#first-user-email").css("border-color", "red");

                        errors.email.forEach(e => {
                            $("#first-user-email-errors").append("<p>" + e + "</p>");
                        });
                    }
                } else if (typeof response.responseJSON.displayMessage != 'undefined') {
                    Toast.fire({
                        icon: 'error',
                        title: response.responseJSON.displayMessage
                    })
                }
            }else{
                Toast.fire({
                    icon: 'error',
                    title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                })
            }
        })
    });

    $.fn.createFirstUserClearForm = function(isDone = false){
        if (isDone) {
            $("#first-user-email").val("");
        }

        $("#first-user-email").css("border-color", "var(--primary)");
        $("#first-user-email-errors").empty();
    }
    // <-- Create first user form

    // Create user form -->
    $(".create-user").click(function() {
        $("#create-user-modal").css("display", "flex");
    });

    $("#create-user-form").on("submit", function(e) {
        e.preventDefault();

        let email = $("#create-user-email").val();
        let csrf = $("#create-user-button").data("csrf");

        $.ajax({
            url: root + "/register",
            type: "POST",
            dataType: "json",
            data: {
                "_token": csrf,
                "email": email
            }
        }).done(function(response) {
            Toast.fire({
                icon: 'success',
                title: response.displayMessage
            })

            $(".modal-box").css("display", "none");

            $.fn.createUserClearForm(true);
        }).fail(function(response) {
            console.log(response);
            $.fn.createUserClearForm();

            if (typeof response.responseJSON != 'undefined') {
                if (response.status === 422) {
                    let errors = response.responseJSON.errors;

                    if (typeof errors.email != 'undefined') {
                        $("#create-user-email").css("border-color", "red");

                        errors.email.forEach(e => {
                            $("#create-user-email-errors").append("<p>" + e + "</p>");
                        });
                    }
                } else if (typeof response.responseJSON.displayMessage != 'undefined') {
                    Toast.fire({
                        icon: 'error',
                        title: response.responseJSON.displayMessage
                    })
                }
            }else{
                Toast.fire({
                    icon: 'error',
                    title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                })
            }
        })
    });

    $.fn.createUserClearForm = function(isDone = false){
        if (isDone) {
            $("#create-user-email").val("");
        }

        $("#create-user-email").css("border-color", "var(--primary)");
        $("#create-user-email-errors").empty();
    }
    // <-- Create user form

    // Change password form -->
    $(".change-pass").click(function() {
        $("#change-pass-modal").css("display", "flex");
    });

    $("#change-pass-form").on("submit", function(e) {
        e.preventDefault();

        let old = $("#change-pass-old").val();
        let new1 = $("#change-pass-new1").val();
        let new2 = $("#change-pass-new2").val();

        let csrf = $("#change-pass-button").data("csrf");

        $.ajax({
            url: root + "/change-password",
            type: "POST",
            dataType: "json",
            data: {
                "_token": csrf,
                "old_password": old,
                "new_password": new1,
                "new_password_confirmation": new2
            }
        }).done(function(response) {
            Toast.fire({
                icon: 'success',
                title: response.displayMessage
            })

            $(".modal-box").css("display", "none");

            $.fn.changePassClearForm(true);
        }).fail(function(response) {
            $.fn.changePassClearForm();

            if (typeof response.responseJSON != 'undefined') {
                if (response.status === 422) {
                    let errors = response.responseJSON.errors;

                    if (typeof errors.old_password != 'undefined') {
                        $("#change-pass-old").css("border-color", "red");

                        errors.old_password.forEach(e => {
                            $("#change-pass-old-errors").append("<p>" + e + "</p>");
                        });
                    }

                    if (typeof errors.new_password != 'undefined') {
                        $("#change-pass-new1").css("border-color", "red");

                        errors.new_password.forEach(e => {
                            $("#change-pass-new1-errors").append("<p>" + e + "</p>");
                        });
                    }
                } else if (typeof response.responseJSON.displayMessage != 'undefined') {
                    Toast.fire({
                        icon: 'error',
                        title: response.responseJSON.displayMessage
                    })
                }
            }else{
                Toast.fire({
                    icon: 'error',
                    title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                })
            }
        })
    });

    $.fn.changePassClearForm = function(isDone = false){
        if (isDone) {
            $("#change-pass-old").val("");
            $("#change-pass-new1").val("");
            $("#change-pass-new2").val("");
        }

        $("#change-pass-old").css("border-color", "var(--primary)");
        $("#change-pass-new1").css("border-color", "var(--primary)");
        $("#change-pass-new2").css("border-color", "var(--primary)");

        $("#change-pass-old-errors").empty();
        $("#change-pass-new1-errors").empty();
        $("#change-pass-new2-errors").empty();
    }
    // <-- Change password form

    // Forgotten password form -->
    $("#forgot-pass-form").on("submit", function(e) {
        e.preventDefault();

        let email = $("#forgot-pass-email").val();
        let csrf = $("#forgot-pass-button").data("csrf");

        $.ajax({
            url: root + "/forgot-password",
            type: "POST",
            dataType: "json",
            data: {
                "_token": csrf,
                "email": email
            }
        }).done(function(response) {
            Toast.fire({
                icon: 'success',
                title: response.displayMessage
            })

            $.fn.forgotPassClearForm(true);
        }).fail(function(response) {
            $.fn.forgotPassClearForm();

            if (typeof response.responseJSON != 'undefined') {
                if (response.status === 422) {
                    if (typeof response.responseJSON != 'undefined') {
                        let errors = response.responseJSON.errors;

                        if (typeof errors.email != 'undefined') {
                            $("#forgot-pass-email").css("border-color", "red");

                            errors.email.forEach(e => {
                                $("#forgot-pass-email-errors").append("<p>" + e + "</p>");
                            });
                        }
                    }
                } else if (typeof response.responseJSON.displayMessage != 'undefined') {
                    Toast.fire({
                        icon: 'error',
                        title: response.responseJSON.displayMessage
                    })
                }
            }else{
                Toast.fire({
                    icon: 'error',
                    title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                })
            }
        })
    });

    $.fn.forgotPassClearForm = function(isDone = false){
        if (isDone) {
            $("#forgot-pass-email").val("");
        }

        $("#forgot-pass-email").css("border-color", "var(--primary)");
        $("#forgot-pass-email-errors").empty();
    }
    // <-- Forgotten password form
    // <-- Authorization forms

    // Closing modals -->
    $(".close-modal").click(function() {
        $(".modal-box").css("display", "none");
    });

    $('.cancel').click(function(){
        $(".modal-box").css("display", "none");
    })
    // <-- Closing modals

    // Account modals -->
    $(".add_account_button i").click(function() {
        $("#create-account-modal").css("display", "flex");
    });

    $(".edit_account").click(function() {
        let account_id = $(this).data("id");
        let account_title = $(this).data("title");
        let account_sap_id = $(this).data("sap");

        $("#edit-account-modal").css("display", "flex");
        $("#edit-account-modal > .modal > #edit-account-form").data("id", account_id);
        $("#edit-account-name").val(account_title);
        $("#edit-account-sap-id").val(account_sap_id);
    });

    $(".delete_account").click(function() {
        let account_id = $(this).data("id");
        $("#delete-account-modal").css("display", "flex");
        $("#delete-account-modal > .modal > #delete-account-form").data("id", account_id);
    });
    // <-- Account modals

    // Financial accounts -->

    $(".account").click(function(){
        var account_id = $(this).data("id");
        window.location.href = root + '/accounts/'+account_id+'/operations';
    });

    // Create financial account form -->
    $("#create-account-form").on("submit", function(e) {
        e.preventDefault();
        $("#create-account-button").attr("disabled", true);

        let title = $("#add-account-name").val();
        let sapId = $("#add-account-sap-id").val();
        let csrf = $("#create-account-button").data("csrf");
        let user_id = $(this).data("user-id");
        let isAdmin = $('body').data('is-admin');
        let urlPath = isAdmin ? "/user/"+ user_id+ "/accounts/" : "/accounts/";
        let url = root + urlPath;
        console.log(url);
        console.log(isAdmin);
        console.log(urlPath);
        console.log($('body').data('is-admin'));

        $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            data: {
                "_token": csrf,
                'title': title,
                'sap_id': sapId
            }
        }).done(function(response) {
            Toast.fire({
                icon: 'success',
                title: response.displayMessage
            })
             location.reload()
            $(".modal-box").css("display", "none");

            $.fn.createAccountClearForm(true);
        }).fail(function(response) {
            console.log(response);
            $.fn.createAccountClearForm();
            if (typeof response.responseJSON != 'undefined'){
                if (response.status === 422) {
                    let errors = response.responseJSON.errors;

                    if (typeof errors.title != 'undefined') {
                        $("#add-account-name").css("border-color", "red");

                        errors.title.forEach(e => {
                            $("#add-account-name-errors").append("<p>" + e + "</p>");
                        });
                    }
                    if (typeof errors.sap_id != 'undefined') {
                        $("#add-account-sap-id").css("border-color", "red");
                        errors.sap_id.forEach(e => {
                            $("#add-account-sap-id-errors").append("<p>" + e + "</p>");
                        });
                    }

                } else if (typeof response.responseJSON.displayMessage != 'undefined') {
                    Toast.fire({
                        icon: 'error',
                        title: response.responseJSON.displayMessage
                    })
                }
            }else{
                console.log("Tu som Tu som1")
                Toast.fire({
                    icon: 'error',
                    title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                })
            }
        })

    });


    $.fn.createAccountClearForm = function(isDone = false){

        if (isDone) {
            $("#add-account-name").val("");
            $("#add-account-sap-id").val("");
        }

        $("#create-account-button").attr("disabled", false);

        $("#add-account-name").css("border-color", "var(--primary)");
        $("#add-account-sap-id").css("border-color", "var(--primary)");

        $("#add-account-name").empty();
        $("#add-account-sap-id").empty();
        $("#add-account-sap-id-errors").empty();
        $("#add-account-name-errors").empty();
    }
    // <-- Create financial account form

    // Edit financial account form -->

    $("#edit-account-form").on("submit", function(e) {
        e.preventDefault();

        let account_id =  $(this).data("id");

        let title = $("#edit-account-name").val();
        let sapId = $("#edit-account-sap-id").val();

        let csrf = $("#edit-account-button").data("csrf");

        $.ajax({
            url: root + "/accounts/" + account_id,
            type: "PUT",
            dataType: "json",
            data: {
                "_token": csrf,
                'title': title,
                'sap_id': sapId
            }
        }).done(function(response) {
            Toast.fire({
                icon: 'success',
                title: response.displayMessage
            })
            location.reload();
            $(".modal-box").css("display", "none");

            $.fn.editAccountClearForm(true);
        }).fail(function(response) {
            console.log(response);
            $.fn.editAccountClearForm();
            if (typeof response.responseJSON != 'undefined'){
                if (response.status === 422) {
                    let errors = response.responseJSON.errors;

                    if (typeof errors.title != 'undefined') {
                        $("#edit-account-name").css("border-color", "red");

                        errors.title.forEach(e => {
                            $("#edit-account-name-errors").append("<p>" + e + "</p>");
                        });
                    }
                    if (typeof errors.sap_id != 'undefined') {
                        $("#edit-account-sap-id").css("border-color", "red");
                        errors.sap_id.forEach(e => {
                            $("#edit-account-sap-id-errors").append("<p>" + e + "</p>");
                        });
                    }

                } else if (typeof response.responseJSON.displayMessage != 'undefined') {
                    Toast.fire({
                        icon: 'error',
                        title: response.responseJSON.displayMessage
                    })
                }
            }else{

                Toast.fire({
                    icon: 'error',

                    title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                })
            }
        })

    });


    $.fn.editAccountClearForm = function(isDone = false){

        if (isDone) {
            $("#edit-account-name").val("");
            $("#edit-account-sap-id").val("");
        }

        $("#edit-account-name").css("border-color", "var(--primary)");
        $("#edit-account-sap-id").css("border-color", "var(--primary)");

        $("#edit-account-name").empty();
        $("#edit-account-sap-id").empty();
        $("#edit-account-sap-id-errors").empty();
        $("#edit-account-name-errors").empty();
    }

    // <-- Edit financial account form

    // Delete financial account form -->

    $("#delete-account-form").on("submit", function(e) {
        e.preventDefault();

        let account_id =  $(this).data("id");

        let csrf = $("#create-account-button").data("csrf");

        $.ajax({
            url: root + "/accounts/" + account_id,
            type: "DELETE",
            dataType: "json",
            data: {
                "_token": csrf
            }
        }).done(function(response) {
            Toast.fire({
                icon: 'success',
                title: response.displayMessage
            })
            location.reload();
            $(".modal-box").css("display", "none");

            $.fn.createAccountClearForm(true);
        }).fail(function(response) {
            console.log(response);
            $.fn.createAccountClearForm();
            if (typeof response.responseJSON != 'undefined'){
                if (response.status === 422) {
                    Toast.fire({
                        icon: 'error',
                        title: response.responseJSON.displayMessage
                    })
                }
            }else{
                Toast.fire({
                    icon: 'error',
                    title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                })
            }
        })

    });

    // <-- Delete financial account form
    // <-- Financial accounts forms
    // <-- Financial accounts

    // --> SAP reports

    $("#reports-filter").click(function(){

        let account_id = $(this).data("account-id");
        let date_from = $('#filter-reports-from').val();
        let date_to = $('#filter-reports-to').val();
        let url = root + '/accounts/'+account_id+'/sap-reports';

        if (date_from != "" || date_to != ""){
            url += '?';
        }
        if (date_from != ""){
            url += 'from=' + date_from
        }
        if (date_to != ""){
            if (date_from != ""){
                url += '&';
            }
            url += 'to=' + date_to
        }
        window.location.href = url;

    });

    // --> SAP reports forms

    // --> add SAP report form
    $("#add-excel-report").click(function(){
        console.log("Button clicked"); // For debugging
        let account_id = $(this).data("account-id");
        $("#add-excel-modal").css("display","flex");
        $("#add-excel-modal > .modal > #create-excel-form").data("account-id", account_id);
    })

    $("#create-excel-form").on("submit", function(e) {
        e.preventDefault();

        $("#create-excel-button").attr("disabled", true);

        let account_id =  $(this).data("account-id")
        console.log("Submitting for Account ID:", account_id); // Add this line to debug


        let csrf = $("#create-excel-button").data("csrf");
        var fileUpload = $("#excel-file").get(0);
        var files = fileUpload.files;
        var fileData = new FormData();
        fileData.append('excel_file', files[0] ?? '');
        fileData.append('_token', csrf);
        console.log(fileData);
        $.ajax({
            url: root + "/accounts/" + account_id + '/excel-upload',
            type: "POST",
            contentType: false,
            processData: false,
            dataType: "json",
            data: fileData
        }).done(function(response) {
            Toast.fire({
                icon: 'success',
                title: response.message
            });
            location.reload();
            $(".modal-box").css("display", "none");

            $.fn.createReportClearForm(true);
        }).fail(function(response) {
            $("#upload-button").attr("disabled", false);
            if (response.responseJSON && response.responseJSON.errors) {
                let errors = response.responseJSON.errors;
                for (let key in errors) {
                    if (errors.hasOwnProperty(key)) {
                        errors[key].forEach(e => {
                            $("#upload-errors").append("<p>" + e + "</p>");
                        });
                    }
                }
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'An error occurred. Please try again later.'
                });
            }
        });
    });


    $("#add-sap-report").click(function(){
        let account_id = $(this).data("account-id");
        $("#add-report-modal").css("display","flex");
        $("#add-report-modal > .modal > #create-report-form").data("account-id", account_id);
    })




    $("#create-report-form").on("submit", function(e){
        e.preventDefault();

        $("#create-report-button").attr("disabled", true);

        let account_id =  $(this).data("account-id")
        console.log("Sdsdsubmitting for Account ID:", account_id); // Add this line to debug
        let csrf = $("#create-report-button").data("csrf");

        var fileUpload = $("#report-file").get(0);
        var files = fileUpload.files;
        var fileData = new FormData();
        fileData.append('sap_report', files[0] ?? '');

        fileData.append('_token', csrf);

        $.ajax({
            url: root + "/accounts/" + account_id + '/sap-reports',
            type: "POST",
            contentType: false, // Not to set any content header
            processData: false, // Not to process data
            dataType: "json",
            data: fileData

        }).done(function(response) {
            Toast.fire({
                icon: 'success',
                title: response.displayMessage
            })


            location.reload();

            $(".modal-box").css("display", "none");

            $.fn.createReportClearForm(true);
        }).fail(function(response) {
            $.fn.createReportClearForm();
            if (typeof response.responseJSON != 'undefined'){
                if (response.status === 422) {
                    let errors = response.responseJSON.errors;

                    if (typeof errors.sap_report != 'undefined') {
                        $("#operation-file").css("border-color", "red");

                        errors.sap_report.forEach(e => {
                            $("#add-sap-report-errors").append("<p>" + e + "</p>");
                        });
                    }

                } else if (typeof response.responseJSON.displayMessage != 'undefined') {
                    Toast.fire({
                        icon: 'error',
                        title: response.responseJSON.displayMessage
                    })
                }
            }else{
                Toast.fire({
                    icon: 'error',
                    title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                })
            }

        })
    });

    $.fn.createReportClearForm = function(isDone = false){

        if (isDone) {
            $("#operation-file").val("");
        }

        $("#create-report-button").attr("disabled", false);

        $("#operation-file").css("border-color", "var(--primary)");
        $("#add-sap-report-errors").css("border-color", "var(--primary)");

        $("#operation-file").empty();
        $("#add-sap-report-errors").empty();
    }
    // <-- add SAP report form

    // --> delete SAP report form

    $(".report-delete").click(function(){
        let report_id = $(this).data("report-id");
        $("#delete-report-form").data("report-id", report_id);
        $("#delete-report-modal").css("display", "flex");
        $("#delete-report-modal").css("display", "flex");
    });

    $("#delete-report-form").on("submit", function(e) {
        e.preventDefault();

        let report_id =  $(this).data("report-id");

        let csrf = $("#delete-report-button").data("csrf");

        $.ajax({
            url: root + "/sap-reports/" + report_id,
            type: "DELETE",
            dataType: "json",
            data: {
                "_token": csrf
            }
        }).done(function(response) {
            Toast.fire({
                icon: 'success',
                title: response.displayMessage
            })
            location.reload();
            $(".modal-box").css("display", "none");

        }).fail(function(response) {
            if (typeof response.responseJSON != 'undefined'){
                if (response.status === 422) {
                    Toast.fire({
                        icon: 'error',
                        title: response.responseJSON.displayMessage
                    })
                }
            }else{
                Toast.fire({
                    icon: 'error',
                    title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                })
            }
        })

    });

    // <-- delete SAP report form
    // <-- SAP reports forms
    // <-- SAP reports

    // Financial accounts filter operations-->

    $("#filter-operations").click(function(){
        let account_id = $(this).data("account-id");
        let date_from = $('#filter-operations-from').val();
        let date_to = $('#filter-operations-to').val();
        let error = $(this).data("date-errors");
        let url = root + '/accounts/'+account_id+'/operations';

        if (date_from != "" || date_to != ""){
            url += '?';
        }
        if (date_from != ""){
            url += 'from=' + date_from
        }
        if (date_to != ""){
            if (date_from != ""){
                url += '&';
            }
            url += 'to=' + date_to
        }
        window.location.href = url;
    });

    // <-- Financial accounts filter operations
    $(".toggle-button").change(function(){
        let account_id = $(this).data("account-id");
        let isAdmin = $('body').data('is-admin');
        let FakeAdmin = $(this).data('fake-admin-id');
        let urlPath;
        if (isAdmin) {
            urlPath = FakeAdmin !== "" ? '/user/' + FakeAdmin + '/accounts/' : "/overview/accounts/";
        } else {
            urlPath = "/accounts/";
        }
        if($(this).attr('checked')){
            window.location.href = root + urlPath + account_id+'/operations';
        }else{
            window.location.href = root + urlPath +account_id+'/sap-reports';
        }
    })

    // Financial operations -->

    // Financial operations export -->

    $("#operations-export").click(function(){

        let account_id = $(this).data("account-id");
        let date_from = $('#filter-operations-from').val();
        let date_to = $('#filter-operations-to').val();
        let url = root + '/accounts/'+account_id+'/operations/export';

        if (date_from != "" || date_to != ""){
            url += '?';
        }
        if (date_from != ""){
            url += 'from=' + date_from
        }
        if (date_to != ""){
            if (date_from != ""){
                url += '&';
            }
            url += 'to=' + date_to
        }
        window.location.href = url;

    });

    // <-- Financial operations export

    // Financial operations detail -->

    $(".operation-detail").click(function(){

        let operation_id = $(this).data("operation-id");
        let csrf = $(this).data("csrf");

        $.ajax({
            url: root + "/operations/" + operation_id,
            type: "GET",
            dataType: "json",
            data: {
                "_token": csrf
            },
            beforeSend: function() {
                $("#loader-modal").css("display", "flex");
                $("#operation-modal").css("display", "none");
            },
            complete: function() {
                $("#loader-modal").css("display", "none");
                $("#operation-modal").css("display", "flex");
            }
        }).done(function(response) {
            if (response.operation.operation_type.expense == 0) {
                $("#operation_main_type").html("Príjem");
            } else {
                $("#operation_main_type").html("Výdavok");
            }

            $("#operation_type").html(response.operation.operation_type.name);
            $("#operation_name").html(response.operation.title);
            $("#operation_subject").html(response.operation.subject);
            $("#operation_sum").html(response.operation.sum + " €");
            date = response.operation.date.substring(0,10);
            dd = date.substring(8,10);
            mm = date.substring(5,7);
            yyyy = date.substring(0,4);
            $("#operation_date").html(dd+"."+mm+"."+yyyy);

            let lending_type = response.operation.operation_type.lending
            let repayment_type = response.operation.operation_type.repayment
            if (lending_type == 1){
                $("#previous-lending-button").css("display", "none");
                if (repayment_type == 1){
                    $("#previous-lending-button").data("previous-id", response.operation.lending.previous_lending_id);
                    $("#previous-lending-button").css("display", "flex");
                    $("#show-repayment-button").css("display", "none");
                    return_date = response.operation.lending.expected_date_of_return
                    if(return_date != null){

                        rdd = return_date.substring(8,10);
                        rmm = return_date.substring(5,7);
                        ryyyy = return_date.substring(0,4);
                        $("#operation_date_until").html(rdd+"."+rmm+"."+ryyyy);

                        $("#operation_date_until_label").css("visibility", "visible")
                        $("#operation_date_until").css("visibility", "visible");

                    }else{
                        $("#operation_date_until").css("visibility", "hidden");
                        $("#operation_date_until_label").css("visibility", "hidden");
                    }
                }else{
                    return_date = response.operation.lending.expected_date_of_return
                    if(return_date != null){
                        $("#operation_date_until_label").css("visibility", "visible")

                        rdd = return_date.substring(8,10);
                        rmm = return_date.substring(5,7);
                        ryyyy = return_date.substring(0,4);
                        $("#operation_date_until").html(rdd+"."+rmm+"."+ryyyy);
                        $("#operation_date_until").css("visibility", "visible");

                    }else{
                        $("#operation_date_until").css("visibility", "hidden");
                        $("#operation_date_until_label").css("visibility", "hidden");
                    }
                    let repaid = response.operation.lending.repayment
                    if (repaid != null){
                        $("#show-repayment-button").data("repay-id", repaid.id);
                        $("#show-repayment-button").css("display", "flex")
                    }else{
                        $("#show-repayment-button").css("display", "none")
                    }

                }

            }else{
                $("#operation_date_until").css("visibility", "hidden");
                $("#operation_date_until_label").css("visibility", "hidden");
                $("#previous-lending-button").css("display", "none");
                $("#show-repayment-button").css("display", "none")
            }

            $("#operation-attachment-button").attr("onclick", 'location.href="'+ root +'/operations/'+ operation_id +'/attachment"')

            if (response.operation.attachment == null){
                $("#operation-attachment-button").css("display", "none");
            }else{
                $("#operation-attachment-button").css("display", "flex");
            }

        }).fail(function(response) {
            if (typeof response.responseJSON != 'undefined'){
                if (response.status === 422) {
                    Toast.fire({
                        icon: 'error',
                        title: response.responseJSON.displayMessage
                    })
                }
            }else{
                Toast.fire({
                    icon: 'error',
                    title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                })
            }
        })
    })

    $("#show-repayment-button").click(function(){
        let operation_id = $(this).data("repay-id");
        $(".modal-box").css("display", "none");
        let csrf = $(this).data("csrf");

        $.ajax({
            url: root + "/operations/" + operation_id,
            type: "GET",
            data: {
                "_token": csrf
            },
            beforeSend: function() {
                $("#loader-modal").css("display", "flex");
                $("#operation-modal").css("display", "none");
            },
            complete: function() {
                $("#loader-modal").css("display", "none");
                $("#operation-modal").css("display", "flex");
            }
        }).done(function(response) {

            if (response.operation.operation_type.expense == 0) {
                $("#operation_main_type").html("Príjem");
            } else {
                $("#operation_main_type").html("Výdavok");
            }

            $("#operation_type").html(response.operation.operation_type.name);
            $("#operation_name").html(response.operation.title);
            $("#operation_subject").html(response.operation.subject);
            $("#operation_sum").html(response.operation.sum + " €");
            date = response.operation.date.substring(0,10);
            dd = date.substring(8,10);
            mm = date.substring(5,7);
            yyyy = date.substring(0,4);
            $("#operation_date").html(dd+"."+mm+"."+yyyy);

            let repayment_type = response.operation.operation_type.repayment

            $("#previous-lending-button").css("display", "none");
            if (repayment_type == 1){
                $("#previous-lending-button").data("previous-id", response.operation.lending.previous_lending_id);
                $("#previous-lending-button").css("display", "flex");
                $("#show-repayment-button").css("display", "none")
                $("#operation_date_until").css("visibility", "hidden");
                $("#operation_date_until_label").css("visibility", "hidden");

            }else{
                $("#operation_date_until_label").css("visibility", "hidden")
                $("#operation_date_until_label").css("visibility", "hidden");

                let repaid = response.operation.lending.repayment
                if (repaid != null){
                    $("#show-repayment-button").data("repay-id", repaid.id);
                    $("#show-repayment-button").css("display", "flex")
                }else{
                    $("#show-repayment-button").css("display", "none")
                }
            }

            $("#operation-attachment-button").attr("onclick", 'location.href="'+ root +'/operations/'+ operation_id +'/attachment"')

            if (response.operation.attachment == null){
                $("#operation-attachment-button").css("display", "none");
            }else{
                $("#operation-attachment-button").css("display", "flex");
            }

        }).fail(function(response) {
            if (typeof response.responseJSON != 'undefined'){
                if (response.status === 422) {
                    Toast.fire({
                        icon: 'error',
                        title: response.responseJSON.displayMessage
                    })
                }
            }else{
                Toast.fire({
                    icon: 'error',
                    title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                })
            }
        })

    })

    $("#previous-lending-button").click(function(){
        let operation_id = $(this).data("previous-id");

        $(".modal-box").css("display", "none");
        let csrf = $(this).data("csrf");

        $.ajax({
            url: root + "/operations/" + operation_id,
            type: "GET",
            data: {
                "_token": csrf
            },
            beforeSend: function() {
                $("#loader-modal").css("display", "flex");
                $("#operation-modal").css("display", "none");
            },
            complete: function() {
                $("#loader-modal").css("display", "none");
                $("#operation-modal").css("display", "flex");
            }
        }).done(function(response) {
            if (response.operation.operation_type.expense == 0) {
                $("#operation_main_type").html("Príjem");
            } else {
                $("#operation_main_type").html("Výdavok");
            }

            $("#operation_type").html(response.operation.operation_type.name);
            $("#operation_name").html(response.operation.title);
            $("#operation_subject").html(response.operation.subject);
            $("#operation_sum").html(response.operation.sum + " €");
            date = response.operation.date.substring(0,10);
            dd = date.substring(8,10);
            mm = date.substring(5,7);
            yyyy = date.substring(0,4);
            $("#operation_date").html(dd+"."+mm+"."+yyyy);

            let repayment_type = response.operation.operation_type.repayment

            $("#previous-lending-button").css("display", "none");
            if (repayment_type == 1){
                $("#previous-lending-button").data("previous-id", response.operation.lending.previous_lending_id);
                $("#previous-lending-button").css("display", "flex");
                $("#show-repayment-button").css("display", "none")
            }else{
                return_date = response.operation.lending.expected_date_of_return
                if (return_date != null){
                    rdd = return_date.substring(8,10);
                    rmm = return_date.substring(5,7);
                    ryyyy = return_date.substring(0,4);
                    $("#operation_date_until").html(rdd+"."+rmm+"."+ryyyy);
                    $("#operation_date_until").css("visibility", "visible");
                    $("#operation_date_until_label").css("visibility", "visible");
                }else{
                    $("#operation_date_until").css("visibility", "hidden");
                    $("#operation_date_until_label").css("visibility", "hidden");
                }
                let repaid = response.operation.lending.repayment
                if (repaid != null){
                    $("#show-repayment-button").data("repay-id", repaid.id);
                    $("#show-repayment-button").css("display", "flex")
                }else{
                    $("#show-repayment-button").css("display", "none")
                }

            }



            $("#operation-attachment-button").attr("onclick", 'location.href="'+ root +'/operations/'+ operation_id +'/attachment"')

            if (response.operation.attachment == null){
                $("#operation-attachment-button").css("display", "none");
            }else{
                $("#operation-attachment-button").css("display", "flex");
            }

        }).fail(function(response) {
            if (typeof response.responseJSON != 'undefined'){
                if (response.status === 422) {
                    Toast.fire({
                        icon: 'error',
                        title: response.responseJSON.displayMessage
                    })
                }
            }else{
                Toast.fire({
                    icon: 'error',
                    title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                })
            }
        })

    })

    // <-- Financial operations detail

    // Financial operations forms -->

    // Delete operation form -->

    $(".operation-delete").click(function(){
        let operation_id = $(this).data("operation-id");
        $("#delete-operation-form").data("operation-id", operation_id);
        $("#delete-operation-modal").css("display", "flex");
    })

    $("#delete-operation-form").on("submit", function(e) {
        e.preventDefault();

        let operation_id =  $(this).data("operation-id");

        let csrf = $("#delete-operation-button").data("csrf");

        $.ajax({
            url: root + "/operations/" + operation_id,
            type: "DELETE",
            dataType: "json",
            data: {
                "_token": csrf
            }
        }).done(function(response) {
            Toast.fire({
                icon: 'success',
                title: response.displayMessage
            })
            location.reload();

            $(".modal-box").css("display", "none");

        }).fail(function(response) {
            console.log(response);
            if (typeof response.responseJSON != 'undefined'){
                if (response.status === 422) {
                    Toast.fire({
                        icon: 'error',
                        title: response.responseJSON.displayMessage
                    })
                }
            }else{
                Toast.fire({
                    icon: 'error',
                    title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                })
            }
        })

    });

    // <-- Delete operation form

    // Check/Uncheck financial operation -->

    $(".financial-operation-check").click(function(){

        let operation_id = $(this).data("operation-id");
        let csrf = $(this).data("csrf");
        let url = root + "/operations/" + operation_id + "/check";

        console.log("We trying to check...");
        console.log("URL for check fin. operation ", url);
        $("#check-operation-form").data("operation-id", operation_id);
        $("#check-operation-modal").css("display", "flex");
        $(".choose-lending").show();
        //$("#check-operation-choice").css("display", "flex");

        $("#check-operation-choice").empty();

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            data: {
                "_token": csrf,
            },
            beforeSend: function() {
                $("#loader-modal").css("display", "flex");
                $("#check-operation-modal").css("display", "none");
            },
            complete: function() {
                $("#loader-modal").css("display", "none");
                $("#check-operation-modal").css("display", "flex");
            }
        }).done(function(response) {
            console.log("RESPONSE \n", response);
            console.log("\n unchecked OPS", response.uncheckedOperations);
            $("#check-operation-choice").append($('<option>', {
                value: "default_opt",
                text: 'Vyberte operáciu'
            }));
            if (response.uncheckedOperations.length != 0){
                response.uncheckedOperations.forEach(function(unchecked_operation){
                    let operation_id = unchecked_operation.id;
                    let operation_title = unchecked_operation.title;
                    let operation_subject = unchecked_operation.subject;
                    let operation_sap_id = unchecked_operation.sap_id;
                    let operation_sum = unchecked_operation.sum;
                    if (unchecked_operation.operation_type.expense)
                    {
                        operation_sum *= -1;
                    }
                    $("#check-operation-choice").append($('<option>',{
                        value: operation_id,
                        text: "TITLE: " + operation_title
                        + (! operation_subject ? "" : ", SUBJECT: " + operation_subject)
                        + ", SAP ID: " + operation_sap_id
                        + ", SUM: " + operation_sum
                    }))
                })
            }
        }).fail(function(response){
            console.log(response);
        })
    })


    $("#check-operation-form").on("submit", function(e) {
        e.preventDefault();

        let operation_id =  $(this).data("operation-id");
        let check_sap_operation_id = $("#check-operation-choice").val();
        let csrf = $("#check-operation-button").data("csrf");

        $.ajax({
            url: root + "/operations/" + operation_id + '/check',
            type: "POST",
            dataType: "json",
            data: {
                '_token': csrf,
                'checked_op_id': check_sap_operation_id
            }
        }).done(function(response) {
            console.log(response);
            Toast.fire({
                icon: 'success',
                title: response.displayMessage
            })
            location.reload();
            $(".modal-box").css("display", "none");

        }).fail(function(response) {
            console.log(response);
            Toast.fire({
                icon: 'error',
                title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
            })

        })

    });

    $(".financial-operation-uncheck").click(function(){

        //let account_id = $(this).data("account-id");
        let operation_id = $(this).data("operation-id");
        let csrf = $(this).data("csrf");
        let url = root + "/operations/" + operation_id + "/uncheck";
        //$("#create-operation-form").data("account-id", account_id);

        console.log("We trying to uncheck...");
        console.log("URL for uncheck fin. operation ", url);
        $("#uncheck-operation-form").data("operation-id", operation_id);
        $("#uncheck-operation-modal").css("display", "none");
        //$("#check-operation-choice").css("display", "flex");

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            data: {
                "_token": csrf,
            },
            beforeSend: function() {
                $("#loader-modal").css("display", "flex");
                $("#uncheck-operation-modal").css("display", "none");
            },
            complete: function() {
                $("#loader-modal").css("display", "none");
                $("#uncheck-operation-modal").css("display", "flex");
            }
        }).done(function(response) {
            console.log("RESPONSE \n", response);

            if (response.operation.operation_type.expense == 0) {
                $("#check_operation_main_type").html("Príjem");
            } else {
                $("#check_operation_main_type").html("Výdavok");
            }
            $("#check_operation_name").html(response.operation.title);
            $("#check_operation_subject").html(response.operation.subject);
            $("#check_operation_sap_id").html(response.operation.sap_id);
            $("#check_operation_sum").html(response.operation.sum + " €");
            date = response.operation.date.substring(0,10);
            dd = date.substring(8,10);
            mm = date.substring(5,7);
            yyyy = date.substring(0,4);
            $("#check_operation_date").html(dd+"."+mm+"."+yyyy);

        }).fail(function(response){
            console.log(response);
            Toast.fire({
                icon: 'error',
                title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
            })
        })
    })

    $("#uncheck-operation-form").on("submit", function(e) {
        e.preventDefault();

        let operation_id =  $(this).data("operation-id");
        let csrf = $("#uncheck-operation-button").data("csrf");

        $.ajax({
            url: root + "/operations/" + operation_id + '/uncheck',
            type: "DELETE",
            dataType: "json",
            data: {
                '_token': csrf,
            }
        }).done(function(response) {
            console.log(response);
            Toast.fire({
                icon: 'success',
                title: response.displayMessage
            })
            location.reload();
            $(".modal-box").css("display", "none");

        }).fail(function(response) {
            console.log(response);
            Toast.fire({
                icon: 'error',
                title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
            })

        })

    });
    // <-- Check/Uncheck financial operation

    // Check/Uncheck SAP operation -->

    $(".sap-operation-check").click(function(){

        let sap_operation_id = $(this).data("sap-operation-id");
        console.log(sap_operation_id);
        let csrf = $(this).data("csrf");
        let url = root + "/sapOperations/" + sap_operation_id + "/check";

        console.log("We trying to check...");
        console.log("URL for check SAP operation ", url);
        $("#check-sap-operation-form").data("sap-operation-id", sap_operation_id);
        $("#check-sap-operation-modal").css("display", "flex");
        $(".choose-lending").show();
        //$("#check-operation-choice").css("display", "flex");

        $("#check-sap-operation-choice").empty();

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            data: {
                "_token": csrf,
            },
            beforeSend: function() {
                $("#loader-modal").css("display", "flex");
                $("#check-sap-operation-modal").css("display", "none");
            },
            complete: function() {
                $("#loader-modal").css("display", "none");
                $("#check-sap-operation-modal").css("display", "flex");
            }
        }).done(function(response) {
            console.log("RESPONSE \n", response);
            console.log("\n unchecked OPS", response.uncheckedOperations);
            $("#check-sap-operation-choice").append($('<option>', {
                value: "default_opt",
                text: 'Vyberte operáciu'
            }));
            if (response.uncheckedOperations.length != 0){
                console.log(response.uncheckedOperations);
                response.uncheckedOperations.forEach(function(operation_data){
                    let unchecked_operation = operation_data[0];
                    let user = operation_data[1];
                    let user_email = user.email;
                    let operation_id = unchecked_operation.id;
                    let operation_title = unchecked_operation.title;
                    let operation_subject = unchecked_operation.subject;
                    let operation_type = unchecked_operation.operation_type.name;
                    let date = unchecked_operation.date.substring(0,10);
                    let dd = date.substring(8,10);
                    let mm = date.substring(5,7);
                    let yyyy = date.substring(0,4);
                    let operation_sum = unchecked_operation.sum;
                    if (unchecked_operation.operation_type.expense)
                    {
                        operation_sum *= -1;
                    }
                    $("#check-sap-operation-choice").append($('<option>',{
                        value: operation_id,
                        text:
                        "EMAIL: " + user_email
                        + ", TITLE: " + operation_title
                        + (! operation_subject ? "" : ", SUBJECT: " + operation_subject)
                        + ", DATE: " + dd+"."+mm+"."+yyyy
                        + ", TYPE: " + operation_type
                        + ", SUM: " + operation_sum
                    }))
                })
            }
        }).fail(function(response){
            console.log(response);
            Toast.fire({
                icon: 'error',
                title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
            })
        })
    })


    $("#check-sap-operation-form").on("submit", function(e) {
        e.preventDefault();

        let sap_operation_id =  $(this).data("sap-operation-id");
        let check_operation_id = $("#check-sap-operation-choice").val();
        let csrf = $("#check-sap-operation-button").data("csrf");

        $.ajax({
            url: root + "/sapOperations/" + sap_operation_id + '/check',
            type: "POST",
            dataType: "json",
            data: {
                '_token': csrf,
                'checked_op_id': check_operation_id
            }
        }).done(function(response) {
            console.log(response);
            Toast.fire({
                icon: 'success',
                title: response.displayMessage
            })
            location.reload();
            $(".modal-box").css("display", "none");

        }).fail(function(response) {
            console.log(response);
            Toast.fire({
                icon: 'error',
                title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
            })

        })

    });

    $(".sap-operation-uncheck").click(function(){

        //let account_id = $(this).data("account-id");
        let sap_operation_id = $(this).data("sap-operation-id");
        let csrf = $(this).data("csrf");
        let url = root + "/sapOperations/" + sap_operation_id + "/uncheck";
        //$("#create-operation-form").data("account-id", account_id);

        console.log("We trying to uncheck...");
        console.log("URL for uncheck SAP operation ", url);
        $("#uncheck-sap-operation-form").data("sap-operation-id", sap_operation_id);
        $("#uncheck-sap-operation-modal").css("display", "none");
        //$("#check-operation-choice").css("display", "flex");

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            data: {
                "_token": csrf,
            },
            beforeSend: function() {
                $("#loader-modal").css("display", "flex");
                $("#uncheck-sap-operation-modal").css("display", "none");
            },
            complete: function() {
                $("#loader-modal").css("display", "none");
                $("#uncheck-sap-operation-modal").css("display", "flex");
            }
        }).done(function(response) {
            console.log("RESPONSE \n", response);

            if (response.operation.operation_type.expense == 0) {
                $("#check_sap_operation_main_type").html("Príjem");
            } else {
                $("#check_sap_operation_main_type").html("Výdavok");
            }
            $("#check_sap_operation_name").html(response.operation.title);
            $("#check_sap_operation_subject").html(response.operation.subject);
            $("#check_sap_operation_sum").html(response.operation.sum + " €");
            date = response.operation.date.substring(0,10);
            dd = date.substring(8,10);
            mm = date.substring(5,7);
            yyyy = date.substring(0,4);
            $("#check_sap_operation_date").html(dd+"."+mm+"."+yyyy);

        }).fail(function(response){
            console.log(response);
            Toast.fire({
                icon: 'error',
                title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
            })
        })
    })

    $("#uncheck-sap-operation-form").on("submit", function(e) {
        e.preventDefault();

        let sap_operation_id =  $(this).data("sap-operation-id");
        let csrf = $("#uncheck-sap-operation-button").data("csrf");

        $.ajax({
            url: root + "/sapOperations/" + sap_operation_id + '/uncheck',
            type: "DELETE",
            dataType: "json",
            data: {
                '_token': csrf,
            }
        }).done(function(response) {
            console.log(response);
            Toast.fire({
                icon: 'success',
                title: response.displayMessage
            })
            location.reload();
            $(".modal-box").css("display", "none");

        }).fail(function(response) {
            console.log(response);
            Toast.fire({
                icon: 'error',
                title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
            })

        })

    });
    // <-- Check/Uncheck SAP operation

    $('input[type=radio][name=operation_type]').change(function() {
        if (this.value == 'loan') {
            $(".choose-lending").show();
            $(".add-operation-expected-date").hide();

            $(".operation-file").hide();
            $("#operation-date-label").html("Dátum splatenia pôžičky");
            $(".add-operation-to").show();
            $(".add-operation-sum").hide();
            $(".add-operation-subject").hide();
            $(".add-operation-name").hide();
            $(".add-operation-choice").hide();

            $(".lending_detail_div").css("display", "none")
            $("#lending_detail").css("display", "none")
            $("#lending-choice").val("default_opt");
            $("#create-operation-button").attr("disabled", false)

            $("#add-operation-to").css("border-color", "var(--primary)");
            $("#add-operation-date-errors").empty();

        } else {
            $(".choose-lending").hide();
            $(".add-operation-expected-date").hide();

            $(".operation-file").show();
            $("#operation-date-label").html("Dátum");
            $(".add-operation-to").show();
            $(".add-operation-sum").show();
            $(".add-operation-subject").show();
            $(".add-operation-name").show();
            $(".add-operation-choice").show();

            $(".lending_detail_div").css("display", "none")
            $("#lending_detail").css("display", "none")
            $("#lending-choice").val("default_opt");
            $("#create-operation-button").attr("disabled", false)
            $("#create-operation-button").attr("disabled", false);

            $("#operation-file").css("border-color", "var(--primary)");
            $("#add-operation-to").css("border-color", "var(--primary)");
            $("#add-operation-type").css("border-color", "var(--primary)");
            $("#add-operation-subject").css("border-color", "var(--primary)");
            $("#add-operation-sum").css("border-color", "var(--primary)");
            $("#add-operation-name").css("border-color", "var(--primary)");
            $("#add-operation-attachment-errors").css("border-color", "var(--primary)");
            $("#add-operation-date-errors").css("border-color", "var(--primary)");
            $("#add-operation-type-errors").css("border-color", "var(--primary)");
            $("#add-operation-subject-errors").css("border-color", "var(--primary)");
            $("#add-operation-sum-errors").css("border-color", "var(--primary)");
            $("#add-operation-title-errors").css("border-color", "var(--primary)");
            $("#add-operation-expected-date").css("border-color", "var(--primary)");
            $("#operation_choice").css("border-color", "var(--primary)");


            $("#operation-file").empty();
            $("#add-operation-to").empty();
            $("#add-operation-type").empty();
            $("#add-operation-subject").empty();
            $("#add-operation-sum").empty();
            $("#add-operation-name").empty();
            $("#add-operation-attachment-errors").empty();
            $("#add-operation-date-errors").empty();
            $("#add-operation-type-errors").empty();
            $("#add-operation-subject-errors").empty();
            $("#add-operation-sum-errors").empty();
            $("#add-operation-title-errors").empty();
            $("#add-operation-expected-date").empty();

        }
    });

    function defaultCreateOperationFormFields(){
        $('input[type=radio][name=operation_type][value="income"]').prop("checked", true)
        $(".choose-lending").hide();
        $(".add-operation-expected-date").hide();
        $(".operation-file").show();
        $("#operation-date-label").html("Dátum");
        $(".add-operation-to").show();
        $(".add-operation-sum").show();
        $(".add-operation-subject").show();
        $(".add-operation-name").show();
        $(".add-operation-choice").show();
        $(".lending_detail_div").css("display", "none")
        $("#lending_detail").css("display", "none")
        $("#lending-choice").val("default_opt");
    }

    // --> Create operation form

    $("#create_operation").click(function(){
        let account_id = $(this).data("account-id");
        let csrf = $(this).data("csrf");
        let isAdmin = false;
        let urlPath = isAdmin ? "/user/"+ user_id+ "/accounts/" : "/accounts/";
        let url = root + urlPath + account_id + "/operations/create";
        $("#create-operation-form").data("account-id", account_id);
        defaultCreateOperationFormFields();
        $(".lending_detail_div").css("display", "none")
        $("#lending_detail").css("display", "none")

        $("#operation_choice").empty();
        $("#lending-choice").empty();

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            data: {
                "_token": csrf,
            },
            beforeSend: function() {
                $("#loader-modal").css("display", "flex");
                $("#create-operation-modal").css("display", "none");
            },
            complete: function() {
                $("#loader-modal").css("display", "none");
                $("#create-operation-modal").css("display", "flex");
            }
        }).done(function(response) {
            //console.log(response);
            $("#operation_choice").append($('<option>', {
                value: "default_opt",
                text: 'Vyberte typ operácie'
            }));
            response.operation_types.forEach(function(choice){
                let expense = choice.expense ? "expense_opt" : "income_opt";
                let lending = choice.lending ? "lending" : "not_lending";
                let type = expense + " " + lending
                $("#operation_choice").append($('<option>', {
                    class: type,
                    value: choice.id,
                    text: choice.name
                }));
            })

            $("#lending-choice").append($('<option>', {
                value: "default_opt",
                text: 'Vyberte pôžičku'
            }));
            if (response.unrepaid_lendings.length != 0){
                response.unrepaid_lendings.forEach(function(unrepaid_lending){
                    let lendind_id = unrepaid_lending.lending.id
                    let lending_title = unrepaid_lending.title
                    $("#lending-choice").append($('<option>',{
                        value: lendind_id,
                        text: lending_title
                    }))
                })
            }
        }).fail(function(response){
            console.log(response);
        })

    })

    $("#create-operation-form").on("submit", function(e) {
        e.preventDefault();
        $("#create-operation-button").attr("disabled", true);

        let csrf = $("#create-operation-button").data("csrf");
        let account_id = $(this).data("account-id");
        let user_id = $(this).data("user-id");
        let isAdmin = $('body').data('is-admin');
        let urlPath = isAdmin ? "/user/"+ user_id+ "/accounts/" : "/accounts/";
        let url = root + urlPath + account_id + "/operations/";

        console.log(url);
        console.log(isAdmin);
        console.log(urlPath);
        console.log($('body').data('is-admin'));
        let expense_income = $("input[name='operation_type']:checked").val();
        let operation_type_id = $("#operation_choice").val();
        let title = $("#add-operation-name").val();
        let subject = $("#add-operation-subject").val();
        let sum = $("#add-operation-sum").val();
        let date = $("#add-operation-to").val();
        let expected_date = $("#add-operation-expected-date").val();
        let lending_id = $("#lending-choice").val();



        var fileUpload = $("#operation-file").get(0);
        var files = fileUpload.files;
        var fileData = new FormData();

        fileData.append('_token', csrf);
        fileData.append('title', title);
        fileData.append('date', date);
        fileData.append('expected_date_of_return', expected_date);
        fileData.append('operation_type_id', operation_type_id);
        fileData.append('subject', subject);
        fileData.append('sum', sum);
        if (files[0] != undefined){
            fileData.append('attachment', files[0] ?? '');
        }

        if ($('input[type=radio][name=operation_type]:checked').val() != 'loan') {
            $.ajax({
                url: url,
                type: "POST",
                contentType: false,
                processData: false,
                dataType: "json",
                data: fileData
            }).done(function(response) {
                Toast.fire({
                    icon: 'success',
                    title: response.displayMessage
                })
                location.reload();

                $(".modal-box").css("display", "none");

                $.fn.createOperationClearForm(true);
            }).fail(function(response) {
                console.log(response);
                $.fn.createOperationClearForm();
                if (typeof response.responseJSON != 'undefined'){
                    if (response.status === 422) {
                        let errors = response.responseJSON.errors;
                        if (typeof errors.attachment != 'undefined') {
                            $("#operation-file").css("border-color", "red");

                            errors.attachment.forEach(e => {
                                $("#add-operation-attachment-errors").append("<p>" + e + "</p>");
                            });
                        }
                        if (typeof errors.date != 'undefined') {
                            $("#add-operation-to").css("border-color", "red");
                            errors.date.forEach(e => {
                                $("#add-operation-date-errors").append("<p>" + e + "</p>");
                            });
                        }
                        if (typeof errors.expected_date_of_return != 'undefined') {
                            $("#add-operation-expected-date").css("border-color", "red");
                            errors.expected_date_of_return.forEach(e => {
                                $("#add-operation-expected-date-errors").append("<p>" + e + "</p>");
                            });
                        }
                        if (typeof errors.operation_type_id != 'undefined') {
                            $("#operation_choice").css("border-color", "red");
                            $("#add-operation-type-errors").append("<p>Neplatný typ operácie.</p>");
                        }
                        if (typeof errors.subject != 'undefined') {
                            $("#add-operation-subject").css("border-color", "red");

                            errors.subject.forEach(e => {
                                $("#add-operation-subject-errors").append("<p>" + e + "</p>");
                            });
                        }
                        if (typeof errors.sum != 'undefined') {
                            $("#add-operation-sum").css("border-color", "red");

                            errors.sum.forEach(e => {
                                $("#add-operation-sum-errors").append("<p>" + e + "</p>");
                            });
                        }
                        if (typeof errors.title != 'undefined') {
                            $("#add-operation-name").css("border-color", "red");

                            errors.title.forEach(e => {
                                $("#add-operation-title-errors").append("<p>" + e + "</p>");
                            });
                        }

                    } else if (typeof response.responseJSON.displayMessage != 'undefined') {
                        Toast.fire({
                            icon: 'error',
                            title: response.responseJSON.displayMessage
                        })
                    }
                }else{
                    Toast.fire({
                        icon: 'error',
                        title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                    })
                }
            })
        }else{
            let option = $("#lending-choice").val()
            let user_id = $(this).data("user-id");
            let isAdmin = $('body').data('is-admin');
            let urlPath = isAdmin ? "/user/"+ user_id + "/operations/" + lending_id + "/repayment": "/operations/" + lending_id + "/repayment";
            let url = root + urlPath;
            if(option != "default_opt"){
                $("#create-operation-button").attr("disabled", false)

                $.ajax({
                    url: url,
                    type: "POST",
                    dataType: "json",
                    data: {
                        "_token": csrf,
                        'date': date
                    }
                }).done(function(response) {
                    Toast.fire({
                        icon: 'success',
                        title: response.displayMessage
                    })
                    $(".modal-box").css("display", "none");

                    location.reload();
                    $.fn.createOperationClearForm(true);
                }).fail(function(response) {
                    $.fn.createOperationClearForm();
                    if (typeof response.responseJSON != 'undefined'){
                        if (response.status === 422) {
                            let errors = response.responseJSON.errors;
                            if (typeof errors.date != 'undefined') {
                                $("#add-operation-to").css("border-color", "red");
                                errors.date.forEach(e => {
                                    $("#add-operation-date-errors").append("<p>" + e + "</p>");
                                });
                            }
                        } else if (typeof response.responseJSON.displayMessage != 'undefined') {
                            Toast.fire({
                                icon: 'error',
                                title: response.responseJSON.displayMessage
                            })
                        }
                    }else{
                        Toast.fire({
                            icon: 'error',
                            title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                        })
                    }
                })
            }else{
                $("#create-operation-button").attr("disabled", true)
            }
        }
    });

    $.fn.createOperationClearForm = function(isDone = false){

        if (isDone) {
            $("#operation-file").val("");
            $("#add-operation-to").val("");
            $("#add-operation-type").val("");
            $("#add-operation-subject").val("");
            $("#add-operation-sum").val("");
            $("#add-operation-name").val("");
            $("#add-operation-expected-date").val("");
        }

        $("#create-operation-button").attr("disabled", false);

        $("#operation-file").css("border-color", "var(--primary)");
        $("#add-operation-to").css("border-color", "var(--primary)");
        $("#add-operation-type").css("border-color", "var(--primary)");
        $("#add-operation-subject").css("border-color", "var(--primary)");
        $("#add-operation-sum").css("border-color", "var(--primary)");
        $("#add-operation-name").css("border-color", "var(--primary)");
        $("#add-operation-attachment-errors").css("border-color", "var(--primary)");
        $("#add-operation-date-errors").css("border-color", "var(--primary)");
        $("#add-operation-type-errors").css("border-color", "var(--primary)");
        $("#add-operation-subject-errors").css("border-color", "var(--primary)");
        $("#add-operation-sum-errors").css("border-color", "var(--primary)");
        $("#add-operation-title-errors").css("border-color", "var(--primary)");
        $("#add-operation-expected-date").css("border-color", "var(--primary)");
        $("#add-operation-expected-date-errors").css("border-color", "var(--primary)");
        $("#operation_choice").css("border-color", "var(--primary)");


        $("#operation-file").empty();
        $("#add-operation-to").empty();
        $("#add-operation-type").empty();
        $("#add-operation-subject").empty();
        $("#add-operation-sum").empty();
        $("#add-operation-name").empty();
        $("#add-operation-attachment-errors").empty();
        $("#add-operation-date-errors").empty();
        $("#add-operation-type-errors").empty();
        $("#add-operation-subject-errors").empty();
        $("#add-operation-sum-errors").empty();
        $("#add-operation-title-errors").empty();
        $("#add-operation-expected-date").empty();
        $("#add-operation-expected-date-errors").empty();
    }

    // <-- Create operation form


    // --> Edit operation form

    $(".operation-edit").click(function(){
        let operation_id = $(this).data("operation-id");
        let csrf = $("#edit-operation-button").data("csrf");

        $("#edit-operation-form").data("operation-id", operation_id);

        $.ajax({
            url: root + "/operations/" + operation_id + "/update",
            type: "GET",
            dataType: "json",
            data: {
                "_token": csrf,
            },
            beforeSend: function() {
                $("#loader-modal").css("display", "flex");
                $("#edit-operation-modal").css("display", "none");
            },
            complete: function() {
                $("#loader-modal").css("display", "none");
                $("#edit-operation-modal").css("display", "flex");
            }
        }).done(function(response) {

            let expense = response.operation.operation_type.expense ? "Výdavok" : "Príjem";

            $("#operation_edit_main_type").html(expense);
            $("#operation_edit_type").html(response.operation.operation_type.name);
            $("#edit-operation-name").val(response.operation.title);
            $("#edit-operation-subject").val(response.operation.subject);
            $("#edit-operation-sum").val(response.operation.sum);
            let date = response.operation.date.substring(0,10);

            $(".add-operation-name").css("display", "flex")
            $(".add-operation-subject").css("display", "flex")
            $(".add-operation-sum").css("display", "flex")
            $(".add-operation-sum").css("display", "flex")
            $(".operation-file").css("display", "flex")
            $("#edit-operation-to").val(date);
            $(".add-operation-expected-date").css("display", "none");
            if (response.operation.operation_type.lending == 1) {

                $(".operation-file").css("display", "none");
                if (response.operation.lending.expected_date_of_return != null){
                    let expected_date = response.operation.lending.expected_date_of_return.substring(0,10);
                    $("#edit-operation-expected-date").val(expected_date);
                }else{
                    $("#edit-operation-expected-date").val('');
                }
                $(".add-operation-expected-date").css("display", "flex");


            }

        }).fail(
            function (response){
                console.log(response);
            })

    })

    $("#lending-choice").change(function(){
        let lending_id = $(this).val()
        let csrf = $("#create-operation-button").data("csrf");
        let option = $("#lending-choice").val()

        if (option != "default_opt"){
            $("#create-operation-button").attr("disabled", false)

            $.ajax({
                url: root + "/operations/" + lending_id,
                type: "GET",
                dataType: "json",
                data: {
                    "_token": csrf
                },
                beforeSend: function() {
                    $("#loader-modal").css("display", "flex");
                },
                complete: function() {
                    $("#loader-modal").css("display", "none");
                }
            }).done(function(response) {
                $("#lending_operation_name").html(response.operation.title);
                $("#lending_operation_subject").html(response.operation.subject);
                $("#lending_operation_sum").html(response.operation.sum + " €");
                date = response.operation.date.substring(0,10);
                dd = date.substring(8,10);
                mm = date.substring(5,7);
                yyyy = date.substring(0,4);
                $("#lending_operation_date").html(dd+"."+mm+"."+yyyy);

                if (response.operation.lending.expected_date_of_return != null){
                    date = response.operation.lending.expected_date_of_return.substring(0,10);
                    ldd = date.substring(8,10);
                    lmm = date.substring(5,7);
                    lyyyy = date.substring(0,4);

                    $("#lending_operation_date_until").html(ldd+"."+lmm+"."+lyyyy);
                }else{
                    $("#lending_operation_date_until_label").css('display', 'none')

                }

                $(".lending_detail_div").css("display", "flex")
                $("#lending_detail").css("display", "flex")
            })
        }else{
            $(".lending_detail_div").css("display", "none")
            $("#lending_detail").css("display", "none")
            $("#create-operation-button").attr("disabled", true)
        }

    })


    $("#edit-operation-form").on("submit", function(e) {
        e.preventDefault();

        $("#edit-operation-button").attr("disabled", true);

        let csrf = $("#edit-operation-button").data("csrf");
        let operation_id = $("#edit-operation-form").data("operation-id");
        let title = $("#edit-operation-name").val();
        let subject = $("#edit-operation-subject").val();
        let sum = $("#edit-operation-sum").val();
        let date = $("#edit-operation-to").val();
        let expected_date = $("#edit-operation-expected-date").val();

        var fileUpload = $("#edit-operation-file").get(0);
        var files = fileUpload.files;
        var fileData = new FormData();

        fileData.append('title', title);
        fileData.append('date', date);
        fileData.append('expected_date_of_return', expected_date);
        fileData.append('subject', subject);
        fileData.append('sum', sum);
        if (files[0] != undefined){
            fileData.append('attachment', files[0] ?? '');
        }
        fileData.append('_token', csrf);
        fileData.append('_method', 'PATCH');

        $.ajax({
            url: root + "/operations/" + operation_id,
            type: "POST",
            contentType: false,
            processData: false,
            dataType: "json",
            data: fileData
        }).done(function(response) {
            Toast.fire({
                icon: 'success',
                title: response.displayMessage
            })
            location.reload();
            $(".modal-box").css("display", "none");

            $.fn.editOperationClearForm(true);
        }).fail(function(response) {
            console.log(response);
            $.fn.editOperationClearForm();
            if (typeof response.responseJSON != 'undefined'){
                if (response.status === 422) {
                    let errors = response.responseJSON.errors;

                    if (typeof errors.attachment != 'undefined') {
                        $("#edit-operation-file").css("border-color", "red");
                        errors.attachment.forEach(e => {
                            $("#edit-operation-attachment-errors").append("<p>" + e + "</p>");
                        });
                    }

                    if (typeof errors.date != 'undefined') {
                        $("#edit-operation-to").css("border-color", "red");
                        errors.date.forEach(e => {
                            $("#edit-operation-date-errors").append("<p>" + e + "</p>");
                        });
                    }
                    if (typeof errors.expected_date_of_return != 'undefined') {
                        $("#edit-operation-expected-date").css("border-color", "red");
                        errors.expected_date_of_return.forEach(e => {
                            $("#edit-operation-expected-date-errors").append("<p>" + e + "</p>");
                        });
                    }
                    if (typeof errors.operation_type_id != 'undefined') {
                        $("#edit-operation-type").css("border-color", "red");
                        $("#edit-operation-type-errors").append("<p>Neplatný typ operácie.</p>");;
                    }
                    if (typeof errors.subject != 'undefined') {
                        $("#edit-operation-subject").css("border-color", "red");

                        errors.subject.forEach(e => {
                            $("#edit-operation-subject-errors").append("<p>" + e + "</p>");
                        });
                    }
                    if (typeof errors.sum != 'undefined') {
                        $("#edit-operation-sum").css("border-color", "red");

                        errors.sum.forEach(e => {
                            $("#edit-operation-sum-errors").append("<p>" + e + "</p>");
                        });
                    }
                    if (typeof errors.title != 'undefined') {
                        $("#edit-operation-name").css("border-color", "red");

                        errors.title.forEach(e => {
                            $("#edit-operation-title-errors").append("<p>" + e + "</p>");
                        });
                    }

                } else if (typeof response.responseJSON.displayMessage != 'undefined') {
                    Toast.fire({
                        icon: 'error',
                        title: response.responseJSON.displayMessage
                    })
                }
            }else{
                Toast.fire({
                    icon: 'error',
                    title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                })
            }
        })

    });

    $.fn.editOperationClearForm = function(isDone = false){

        if (isDone) {
            $("#operation-file").val("");
            $("#edit-operation-to").val("");
            $("#edit-operation-type").val("");
            $("#edit-operation-subject").val("");
            $("#edit-operation-sum").val("");
            $("#edit-operation-name").val("");
        }

        $("#edit-operation-button").attr("disabled", false);

        $("#operation-file").css("border-color", "var(--primary)");
        $("#edit-operation-to").css("border-color", "var(--primary)");
        $("#edit-operation-type").css("border-color", "var(--primary)");
        $("#edit-operation-subject").css("border-color", "var(--primary)");
        $("#edit-operation-sum").css("border-color", "var(--primary)");
        $("#edit-operation-name").css("border-color", "var(--primary)");
        $("#edit-operation-attachment-errors").css("border-color", "var(--primary)");
        $("#edit-operation-date-errors").css("border-color", "var(--primary)");
        $("#edit-operation-expected-date").css("border-color", "var(--primary)");
        $("#edit-operation-expected-date-errors").css("border-color", "var(--primary)");
        $("#edit-operation-type-errors").css("border-color", "var(--primary)");
        $("#edit-operation-subject-errors").css("border-color", "var(--primary)");
        $("#edit-operation-sum-errors").css("border-color", "var(--primary)");
        $("#edit-operation-title-errors").css("border-color", "var(--primary)");

        $("#operation-file").empty();
        $("#edit-operation-to").empty();
        $("#edit-operation-type").empty();
        $("#edit-operation-subject").empty();
        $("#edit-operation-sum").empty();
        $("#edit-operation-name").empty();
        $("#edit-operation-attachment-errors").empty();
        $("#edit-operation-date-errors").empty();
        $("#edit-operation-type-errors").empty();
        $("#edit-operation-subject-errors").empty();
        $("#edit-operation-sum-errors").empty();
        $("#edit-operation-title-errors").empty();
        $("#edit-operation-expected-date").empty();
        $("#edit-operation-expected-date-errors").empty();
    }

    // <-- Edit operaton form

    // --> Repay lending form

    $(".operation-repayment").click(function(){
        let operation_id = $(this).data("operation-id");
        $("#repay-lending-modal > .modal > #repay-lending-form").data("operation-id", operation_id);
        $("#repay-lending-modal").css("display", "flex");
    })

    $("#repay-lending-form").on("submit", function(e) {
        e.preventDefault();

        $("#repay-lending-button").attr("disabled", true);

        let date = $("#repay-lending-date").val();
        let csrf = $("#repay-lending-button").data("csrf");
        let operation_id = $(this).data("operation-id");
        let user_id = $(this).data("user-id");
        let isAdmin = $('body').data('is-admin');
        let urlPath = isAdmin ? "/user/"+ user_id + "/operations/" + operation_id + "/repayment": "/operations/" + operation_id + "/repayment";
        let url = root + urlPath;

        $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            data: {
                "_token": csrf,
                'date': date
            }
        }).done(function(response) {
            Toast.fire({
                icon: 'success',
                title: response.displayMessage
            })
            $(".modal-box").css("display", "none");

            location.reload();
            $.fn.repaymentClearForm(true);
        }).fail(function(response) {

            $.fn.repaymentClearForm();
            if (typeof response.responseJSON != 'undefined'){
                if (response.status === 422) {
                    let errors = response.responseJSON.errors;
                    if (typeof errors.date != 'undefined') {
                        $("#repayment-operation-date").css("border-color", "red");
                        errors.date.forEach(e => {
                            $("#repay-lending-date-errors").append("<p>" + e + "</p>");
                        });
                    }else if (typeof response.responseJSON.displayMessage != 'undefined') {
                        Toast.fire({
                            icon: 'error',
                            title: response.responseJSON.displayMessage
                        })
                    }
                }
            }else{
                Toast.fire({
                    icon: 'error',
                    title: 'Niečo sa pokazilo. Prosím, skúste to neskôr.'
                })
            }
        })

    });

    $.fn.repaymentClearForm = function(isDone = false){
        if (isDone) {
            $("#repayment-operation-date").val("");
        }

        $("#repay-lending-button").attr("disabled", false);

        $("#repayment-operation-date").css("border-color", "var(--primary)");
        $("#repayment-operation-date").empty();
        $("#repay-lending-date-errors").css("border-color", "var(--primary)");
        $("#repay-lending-date-errors").empty();

    }

    // <-- Repay lending form

    function updateSelectOptions(operation_type){
        switch(operation_type){
            case 'income':
                $(".expense_opt").css("display","none")
                $(".income_opt").css("display","flex")
                $("#operation_choice").val("default_opt")
                $("#edit_operation_choice").val("default_opt")
                $(".lending_opt").css("display","none")
                $(".edit_lending_opt").css("display","none")
                break;
            case 'expense':
                $(".income_opt").css("display","none")
                $(".expense_opt").css("display","flex")
                $("#operation_choice").val("default_opt")
                $("#edit_operation_choice").val("default_opt")
                $(".lending_opt").css("display","none")
                $(".edit_lending_opt").css("display","none")
                break;
        }
    }

    $(".operation_type").change(function(){
        updateSelectOptions($(this).val());
    });

    // 3 -> lending to
    // 10 -> lending from
    function updateOperationForm(operation_category){
        if(operation_category == "3" ||
            operation_category == "10"){
            $(".add-operation-name").css("display","flex");
            $(".add-operation-subject").css("display","flex");
            $(".add-operation-sum").css("display","flex");
            $(".add-operation-to").css("display","flex");
            $(".add-operation-expected-date").css("display","flex");
            $(".operation-file").css("display","none");
            $(".choose-lending").css("display","none");
            return;
        }
        $(".add-operation-name").css("display","flex");
        $(".add-operation-subject").css("display","flex");
        $(".add-operation-sum").css("display","flex");
        $(".add-operation-to").css("display","flex");
        $(".add-operation-expected-date").css("display","none");
        $(".operation-file").css("display","flex");
        $(".choose-lending").css("display","none");

    }

    // 3 -> lending to
    // 10 -> lending from
    $("#operation_choice").change(function(){
        updateOperationForm($(this).val());
    })

    $("#edit_operation_choice").change(function(){
        updateOperationForm($(this).val());
    })

    // <-- Financial operations forms

    // <-- Financial operations

//admin
$(".user").click(function(){
    var user_id = $(this).data("id");
    window.location.href = root + '/user/'+ user_id +'/accounts';
});


$(".overview_account").click(function(){
    var user_id = $(this).data("user_id");
    var account_id = $(this).data("id");
    console.log(user_id,account_id);
    window.location.href = root + '/overview/accounts/' +account_id+'/operations';
});

$(".account_admin").click(function(){
    var user_id = $(this).data("user_id");
    var account_id = $(this).data("id");
    console.log(user_id,account_id);
    window.location.href = root + '/user/'+ user_id + '/accounts/'+account_id+'/operations';
});


})

function admin_user_overview(row) {
    var user_id = row.getAttribute('data-id');
    window.location.href = root + '/user/'+ user_id +'/accounts';
}


