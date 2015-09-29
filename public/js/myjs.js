$(document).ready(function () {
    $('.profile-img img').click(function () {
        $('.showImg').fadeIn();
        console.log(this.getAttribute('src'));
        console.log(this.id);
        $.ajax({
            type: 'POST',
            url: 'user/img',
            data: 'id=' + this.id,
            success: function(data){
                $('.results').html(data);
            }
        });



    });

    $('.close').click(function () {
        $('.showImg').fadeOut();
    });

    $.ajax({
        type: 'POST',
        url: 'user/search',
        success: function(data) {
            //$('.results').html(data);
            console.log(data);
        }
    });



});