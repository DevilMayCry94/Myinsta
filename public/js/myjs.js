$(document).ready(function () {
    $('.profile-img img').click(function () {
        $('.showImg').fadeIn();
        var src = this.getAttribute('src');
        var id =this.id;
        showComment(id,src);
        sendComment(id,src);
        function f() {loadComment(1)}
        setInterval(f,1000);

    });

    function showComment(id,src) {
        $.ajax({
            type: 'POST',
            url: '/ajax/index',
            data: 'idImg=' + id + '&src=' + src ,
            dataType: 'JSON',
            success: function (data) {
                console.log(JSON.stringify(data));
                var comment = "";
                for (var j in data["comment"]) {
                    comment += "<li>"
                        + '    <a href="?id=' + data["comment"][j]["idUser"]
                        + '          ">' + data["comment"][j]["name"] + '</a> ' + data["comment"][j]["comment"] + ''
                        + '  </li>';
                }
                ////???
                var header = '<img src="img/' + data["inf_user"]["ava"] + '"/>'
                    + '  <span> ' + data.inf_user.name + ' </span>';
                var img = '<img src="' + data.src + '"/>';
                var li = '<li><a href="?id=0">' + data["inf_user"]["name"] + '</a> ' + data["own_comment"] + '</li>'
                    + comment;

                $("#ImgWithComment header").append(header);
                $('.img-block').append(img);
                $('#comments').append(li);
            }
        });
    }

    function sendComment(id,src) {
        $('#btn-send-comment').click(function(){
            var comment = $('#comment-text').val();
            if (comment != "") {
                $.ajax({
                    type: 'POST',
                    url: '/ajax/myComment',
                    data: 'idImg=' + id + '&src=' + src + '&textcomment=' + comment,
                    dataType: 'JSON',
                    success: function (data) {
                        console.log(data);
                        mycomment = "<li>"
                            + '    <a href="#"' + data["idUser"]
                            + '          ">' + data["name"] + '</a> ' + data["comment"] + ''
                            + '  </li>';
                        $("#comments").append(mycomment);
                    }
                });
                $('#comment-text').val('');
            }
        });
    }


    function loadComment(id)
    {
        var count = 0;
        $.ajax({
            type: 'POST',
            url: '/ajax/loadComment',
            data: 'idPost=' + id + '&count=' + count,
            dataType: 'JSON',
            success: function(data) {
                console.log(data);
            }
        });
        //console.log(id);
    }

    $('.close').click(function () {
        $('.showImg').fadeOut();
        $('#ImgWithComment header').empty();
        $('.img-block').empty();
        $('#comments').empty();
    });
    $("#search").keyup(function() {
        var searchval = $('#search').val();
        if(searchval != "") {
            $('.dropdown-menu').fadeIn();
            $.ajax({
                type: 'POST',
                url: '/ajax/search',
                data: 'search=' + searchval,
                dataType: 'JSON',
                success: function (data) {
                    console.log(data[0]['ava']);
                    $('.dropdown-menu').empty();
                    for (var i in data) {
                        var result = "<li>"
                            +"  <a tabindex='-1' href='/user?id=" + data[i]['id'] + " '>"
                            + ' <img src="img/' + data[i]['ava'] +'" class="img-circle"/>    '
                            + '              ' + data[i]['name']
                            + '           </a></li>';
                        $('.dropdown-menu').append(result);
                    }
                }
            });
        } else {
            $('.dropdown-menu').empty();
            $('.dropdown-menu').fadeOut();
        }
    });

    $('.follow').click(function(){
        var idUser = this.getAttribute('name');
        var btnValue = $.trim($('.follow').text());
        $.ajax({
            type: 'POST',
            url: '/ajax/follow',
            data: 'idFollower=' + idUser + '&btnValue=' + btnValue,
            success: function(data){
                $('.follow').text(data['value']);
            }
        });
    });

    $('#loadMore').click(function(){
        var countPost = $('.myphoto img').length;
        var userLink = $('#userLink')[0].getAttribute('name');
        console.log(countPost);
        console.log(userLink);
        $.ajax({
            type: 'POST',
            url: '/ajax/loadMore',
            data: 'count=' + countPost + '&linkUser=' + userLink,
            success: function(data){
                console.log(data[0]['urlImg'].split('/'));
                for(var d in data)
                {
                    if(d == data.length-1) break;
                    var urlImg = data[d]['urlImg'].split('/')[7];
                    var post = '<div class="profile-img">'
                    + '<a ><img src="/img/' + urlImg + '" id="img_' + data[d]["id"] + '"/></a>'
                    +'<div class="like-comment">'
                    +'<span class="glyphicon glyphicon-heart-empty">1</span>'
                    +'</div>'
                    +'</div>';
                    $('.myphoto').append(post);
                }
                if(data[data.length-1] == 1)
                {
                    $('#loadMore').remove();
                }
            }
        });
    });

});