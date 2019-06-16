require('../css/app.scss');
var $ = require('jquery');
require('bootstrap');

function getResults(list, url) {
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
        success: function (results) {
            if (list === 1) {
                $("#operations_total").hide();
                $("#list_table").find('tbody').empty();
                $.each(JSON.parse(results), function (k, result) {
                    $.each(result, function (libelle, operation) {
                        $("#operations_list").show();
                        $("#list_table").find('tbody')
                            .append($('<tr>')
                                .append($('<td>')
                                    .text(operation['date'])
                                )
                                .append($('<td>')
                                    .text(libelle)
                                )
                                .append($('<td>')
                                    .text(operation['recette'] + ' €')
                                )
                                .append($('<td>')
                                    .text(operation['depense'] + ' €')
                                )
                            );
                    })
                });
            } else {
                $("#operations_list").hide();
                $("#operations_total").show();
                if (results < 0) {
                    $("#operations_total_result").html(results + ' €').css('color', 'red');
                } else {
                    $("#operations_total_result").html(results + ' €').css('color', 'green');
                }
            }
        },
        error: function (jxh, textmsg, errorThrown) {
            console.log(textmsg);
            console.log(errorThrown);
        }
    });
}

$(document).ready(function(){
    $('#start_date').on('input',function(){
        $('#start_date').attr('value', $('#start_date').val());
        $('#end_date').attr('min', $('#start_date').val());
    });
    $('#end_date').on('input',function(){
        $('#end_date').attr('value', $('#end_date').val());
        $('#start_date').attr('max', $('#end_date').val());
    });

    $('#submit_list_button').click(function () {
        let url = $('#submit_list_button').data('url');
        let list = 1;
        getResults(list, url);
    });

    $('#submit_total_button').click(function () {
        let url = $('#submit_total_button').data('url');
        let list = 0;
        getResults(list, url);
    });
});
