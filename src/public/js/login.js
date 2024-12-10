$(document).ready(function(){
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
})