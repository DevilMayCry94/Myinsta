$(document).ready(function () {
    $('.profile-post').on('click', function () {
        $('.showImg').fadeIn();
        src = "";
        src = this.getAttribute('src');
        id = this.id;
        showComment(id, src);
        function f() {
            loadComment(src)
        }

        commentLoad = setInterval(f, 1000);

    });

    function showComment(id, src) {
        console.log(id + " -> "+ src);
        $.ajax({
            type: 'POST',
            url: '/ajax/index',
            data: 'idImg=' + id + '&src=' + src,
            success: function (data) {
                console.log(data);
                var comment = "";
                for (var j in data["comment"]) {
                    comment += '<li class="Comm" data-reactid="' + data["comment"][j]['idAction'] + '">'
                        + '<img data-after="' + data["comment"][j]["ava"] + '" />'
                        + '<a href="#">' + data["comment"][j]["name"] + '</a>'
                        + '<span>' + data["comment"][j]["created"] + '</span>'
                        + '<p>' + data["comment"][j]["comment"] + '</p>'
                        + '</li>';

                }

                ////???
                var header = '<img src="img/' + data["inf_user"]["ava"] + '"/>'
                    + '  <span> ' + data["inf_user"]["name"] + ' </span>';
                var img = '<img src="' + data["src"] + '"/>';
                var li = '<li><a href="?id=0">' + data["inf_user"]["name"] + '</a> ' + data["own_comment"] + '</li>'
                    + comment;

                $("#ImgWithComment header").append(header);
                $('.img-block').append(img);
                $('#comments').append(li);
            }
        });
    }

    $('#btn-send-comment').click(function () {
        var comment = $('#comment-text').val();
        if (comment != "") {
            var currentdate = new Date();
            var hours = (currentdate.getHours() < 10) ? "0" + currentdate.getHours() : currentdate.getHours();
            var minutes = (currentdate.getMinutes() < 10) ? "0" + currentdate.getMinutes() : currentdate.getMinutes();
            var seconds = (currentdate.getSeconds() < 10) ? "0" + currentdate.getSeconds() : currentdate.getSeconds();
            var month = (currentdate.getMonth() + 1 < 10) ? "0" + currentdate.getMonth() + 1 : currentdate.getMonth() + 1;
            var date = (currentdate.getDate() < 10) ? "0" + currentdate.getDate() : currentdate.getDate();
            var datetime = ""
                + currentdate.getFullYear() + "-"
                + month + "-"
                + date + " "
                + hours + ":"
                + minutes + ":"
                + seconds;
            lastli = $('#comments li').last();
            var lastid = lastli.data("reactid") + 1;
            $.ajax({
                type: 'POST',
                url: '/ajax/myComment',
                data: 'idImg=' + id + '&src=' + src + '&textcomment=' + comment,
                dataType: 'JSON'
            });
            $('#comment-text').val('');
        }
    });


    function loadComment(src) {
        var lastli = $('#comments li').last();
        var lastcomment = lastli.data("reactid");
        $.ajax({
            type: 'POST',
            url: '/ajax/loadComment',
            data: 'src=' + src + '&lastid=' + lastcomment,
            dataType: 'JSON',
            success: function (data) {
                for (var i in data) {
                    var newcomment = '<li class="Comm" data-reactid="' + data[i]["idAction"] + '">'
                        + '<img data-after="' + data[i]["ava"] + '" />'
                        + '<a href="#">' + data[i]["name"] + '</a>'
                        + '<span>' + data[i]["created"] + '</span>'
                        + '<p>' + data[i]["comment"] + '</p>'
                        + '</li>'
                    $("#comments").append(newcomment);
                }
            }
        });
    }

    $('.close').click(function () {
        $('.showImg').fadeOut();
        $('#ImgWithComment header').find('*').not('button').remove();
        $('.img-block').empty();
        $('#comments').empty();
        clearInterval(commentLoad);

    });
    $("#search").keyup(function () {
        var searchval = $('#search').val();
        if (searchval != "") {
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
                            + "  <a tabindex='-1' href='/user?id=" + data[i]['id'] + " '>"
                            + ' <img src="img/' + data[i]['ava'] + '" class="img-circle"/>    '
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

    $('.follow').click(function () {
        var idUser = this.getAttribute('name');
        var btnValue = $.trim($('.follow').text());
        $.ajax({
            type: 'POST',
            url: '/ajax/follow',
            data: 'idFollower=' + idUser + '&btnValue=' + btnValue,
            success: function (data) {
                $('.follow').text(data['value']);
            }
        });
    });

    $('#loadMore').on('click', function () {
        var countPost = $('.profile-img').length;
        var userLink = $('#userLink')[0].getAttribute('name');
        $.ajax({
            type: 'POST',
            url: '/ajax/loadMore',
            data: 'count=' + countPost + '&linkUser=' + userLink,
            success: function (data) {
                console.log(data);
                for (var d in data) {
                    if (d == data.length - 1) break;
                    var urlImg = data[d]['urlImg'].split('/')[7];
                    var post = '<div class="profile-img">'
                        + '<a ><img src="/img/' + urlImg + '" id="img_' + data[d]["id"] + '"/></a>'
                        + '<div class="like-comment">'
                        + '<img src="img/like.png" > <span>0</span>'
                        + '<img src="img/comment.png" > <span>0</span>'
                        + '</div>'
                        + '</div>';
                    $('.myphoto').append(post);
                }
                if (data[data.length - 1] == 1) {
                    $('#loadMore').remove();
                }
            }
        });
    });

    $('.like-comment .profile-like').on('click',function(){
        var self = this;
        like(self);
    });

    $('.profile-img').mouseover(function () {
        $(this).find('.like-comment').css('display', 'block');
        //var target = $( e.target );
        //target.find('.like-comment').css('display','block');
    });

    $('.profile-img').mouseout(function () {
        //var target = $( e.target );
        //target.find('.like-comment').css('display','none');
        $(this).find('.like-comment').css('display', 'none');
    });

    //news

    $('.news > ul > li').on('click', 'a', function () {
        //$('.news > ul').find('.active').removeClass('active');
        //$(this).addClass('active');
        console.log("a");
    });

    $('.like-icon img').on('click', function(){
        var self = this;
        like(self);
    });

    function like(self) {
        var isLike = $(self).hasClass('like') ? 'true' : 'false';
        var idPost = $(self).parent().parent().parent().parent().find('.new-post').data('post');
        if(isLike == 'true') {
            $(self).removeClass('like');
            $(self).attr('src','/img/unlike.png');
        } else {
            $(self).addClass('like');
            $(self).attr('src','/img/like.png');
        }
        $.ajax({
            method: 'POST',
            url: '/ajax/newsLike',
            data: 'isLike=' + isLike + '&idPost=' + idPost,
            success: function(data) {
            }
        });
    }

    //croppic img

    var croppicContainerModalOptions = {
        uploadUrl:'/ajax/imgsave',
        cropUrl:'/ajax/cropfile',
        modal:true,
        imgEyecandyOpacity:0.4,
        loaderHtml:'<div class="loader bubblingG"><span id="bubblingG_1"></span><span id="bubblingG_2"></span><span id="bubblingG_3"></span></div> ',
        onBeforeImgUpload: function(){ console.log('onBeforeImgUpload') },
        onAfterImgUpload: function(){ console.log('onAfterImgUpload') },
        onImgDrag: function(){ console.log('onImgDrag') },
        onImgZoom: function(){ console.log('onImgZoom') },
        onBeforeImgCrop: function(){ console.log('onBeforeImgCrop') },
        onAfterImgCrop:function(){ console.log('onAfterImgCrop') },
        onReset:function(){ console.log('onReset') },
        onError:function(errormessage){ console.log('onError:'+errormessage) }
    }

    var cropperHeader = new Croppic('cropContainerHeader', croppicContainerModalOptions);

    $('#btn-send-post').click(function(){
        var src_img = $('#cropContainerHeader > img')[0].getAttribute('src');
        var own_comment = $('.form-group > textarea').val();
        if(src_img != "") {
            $.ajax({
                type: 'POST',
                url: '/ajax/addpost',
                data: 'src_img=' + src_img + '&comment=' + own_comment,
                success: function(data) {
                    var post = '<div class="profile-img">'
                        +'<a><img src="'+ src_img +'" class="profile-post"/></a>'
                        +'<div class="like-comment">'
                        +'<img src="img/like.png"> <span>0</span>'
                        +'<img src="img/comment.png"> <span>0</span>'
                        +'</div>'
                        +'</div>';
                    $('.myphoto').prepend(post);
                    $('#cropContainerHeader img').remove();
                }
            });
        } else {
            alert('please upload your photo!!!');
        }
    });

    //ava
    $('#ImgWithComment header > button').on('click',function(){
        var src_ava = $('.img-block img')[0].getAttribute('src');
        $.ajax({
            type: 'POST',
            url: '/ajax/changeAva',
            data: 'src_ava=' + src_ava,
            success: function(data) {
                $('.ava > img')[0].setAttribute('src', src_ava);
            }
        });
    });
    //edit profile

    $('#sidebar-wrapper li').on('click', function(){
        $('#sidebar-wrapper').find('.li-active').removeClass('li-active');
        $('.row').find('.edit-active').removeClass('edit-active');
        $(this).addClass('li-active');
        var block = $(this).data('nameblock');
        var classBlock = "."+ block;
        $(classBlock).addClass('edit-active');
    });


});