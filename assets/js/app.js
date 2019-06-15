require('../css/app.css');
var $ = require('jquery');

$(document).ready(function(){
    $('#start_date').on('input',function(){
        $('#start_date').attr('value', $('#start_date').val());
        $('#end_date').attr('min', $('#start_date').val());
    });
    $('#end_date').on('input',function(){
        $('#end_date').attr('value', $('#end_date').val());
        $('#start_date').attr('max', $('#end_date').val());
    });

    $('#submit_button').click(function () {
        let url = $('#submit_button').data('url');
        let rib = $('#rib').children("option:selected").val();
        let startDate = $('#start_date').val();
        let endDate = $('#end_date').val();

        if (startDate && endDate) {
            if (startDate > endDate) {
                alert('La date de début ne peut être postérieure à la date de fin !');
            } else if (endDate < startDate) {
                alert('La date de fin ne peut être antérieure à la date de début !');
            }
        }

        $.ajax({
            type: "POST",
            url: url,
            data: {rib: rib, startDate: startDate, endDate: endDate},
            dataType: "html",
        }).done( function(response) {
            // alert(123);
            //var htmlToDisplay = response.trim();
            console.log(response);
            $("#operations_results").html(response);
        }).fail(function(jxh,textmsg,errorThrown){
            console.log(textmsg);
            console.log(errorThrown);
        });
    });
});
