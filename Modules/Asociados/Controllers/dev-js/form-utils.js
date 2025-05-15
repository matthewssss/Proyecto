$(document).on('change', '#fecha_nacimiento, #inputFechaNacimiento', function(event) {
    const targetId = event.target.id;

    const birthDate = new Date(this.value);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    
    // Adjust age if birthday hasn't occurred this year
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    // Set age
    targetId === 'fecha_nacimiento' ? $('#edad').val(age) : $('#inputEdad').val(age);
    
    // Set unit based on age
    let unit = '';
    let continuar = true;

    if (age < 9) {
        unit = 'Aun no puede entrar en nuestro grupo';
        continuar = false;
    }
    else if (age >= 9 && age <= 11) unit = 'Lobatos';
    else if (age >= 12 && age <= 14) unit = 'Exploradores';
    else if (age >= 15 && age <= 17) unit = 'Pioneros';
    else if (age >= 18 && age <= 21) unit = 'Rutas';
    else if (age > 21) {
        unit = 'Usted es kraal, no puede registrarse aquí';
        continuar = false;
    } 
    
    targetId === 'fecha_nacimiento' ? $('#unidad').val(unit) : $('#inputUnidad').val(unit);

    // Fix the button disable functionality
    if (!continuar) {
        if (targetId === 'fecha_nacimiento') {
            $('#id_btnSiguiente').prop('disabled', true);
            $('#id_btnSiguiente').css('opacity', '0.5');
            
            disableButtonsStep('step-2');
            disableButtonsStep('step-3');
        } else {
            $(`#${targetId}`).addClass('is-invalid');
            $('#inputUnidad').addClass('is-invalid');
            $('#inputEdad').addClass('is-invalid');
            $('#fechaErrorRama').text(unit);
        }
    } else {
        if (targetId === 'fecha_nacimiento') {
            $('#id_btnSiguiente').prop('disabled', false);
            $('#id_btnSiguiente').css('opacity', '1');

            enableButtonsStep('step-2');
            enableButtonsStep('step-3');
        } else {
            $(`#${targetId}`).removeClass('is-invalid');
            $('#inputUnidad').removeClass('is-invalid');
            $('#inputEdad').removeClass('is-invalid');
            $('#fechaErrorRama').text('');

        }

    }
});


function disableButtonsStep (step) {
    var stepButton = $('a[href="#' + step + '"]');
    stepButton.addClass('disabled').attr('disabled', 'disabled');
}

function enableButtonsStep(step) {
    var stepButton = $('a[href="#' + step + '"]');
    stepButton.removeClass('disabled').removeAttr('disabled');
}

