$('#password').focus(function (){
    $('#signup').attr('disabled', true);
});

$('#password').focusout(function (){
    $('#password1').focus();
});

$('#password1').focusout(function (){
    $('#signup').attr('disabled', false);
    if (($('#password').val()).localeCompare($('#password1').val()) != 0) { 
        $('#password').val("");
        $('#password1').val("");
        alert('Пароли не совпадают');
    }
});

/*$('#password1').keypress(function (event){
    if (event.which == 13) {
      //  $('#signup').attr('disabled', false);
      //  $('#signup').focus();
    }
});*/

    
    