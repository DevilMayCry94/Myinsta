$(document).ready(function () {
    $('.profile-img img').click(function () {
        $('.showImg').fadeIn();
        $.ajax({
            type: 'POST',
            url: 'application/user/showimg',
            data: 'idImg=' + this.id + '&src=' + this.getAttribute('src'),
            dataType: 'JSON',
            success: function(data) {
                console.log(data['response'].src);
                $(".ImgWithComment").text("");
                //???
                    var img = "<div class='img-block'>"
                        +'          <table>'
                        +'              <tr>'
                        +'                  <td rowspan="3">'
                        +                       '<img src="' + data['response'].src + '"/>'
                        +'                  </td>'
                        +'                  <td class="inf-img">'
                        +'                       info'
                        +'                  </td>'
                        +'              </tr>'
                        +'             <tr>'
                        +'                  <td class="comment-img">'
                        +'                       comment'
                        +'                  </td>'
                        +'              </tr>'
                        +'             <tr>'
                        +'                  <td class="my-comment">'
                        +'                      <form action="user/comment" method="post" class="form-inline">'
                        +'                      <div class="form-group inp-comment">'
                        +'                          <input type="text" name="yourcomment" placeholder="Comment..." class="form-control"/>'
                        +'                      </div>'
                        +'                          <button type="submit" class="btn btn-default">Send</button>'
                        +'                      </form>'
                        +'                  </td>'
                        +'              </tr>'
                        +'          </table>'
                        +'     </div>';
                    //console.log(product);
                    $(".ImgWithComment").append(img);
            }
        });


    });

    $('.close').click(function () {
        $('.showImg').fadeOut();
    });
    $("#search").keyup(function() {
        //$('.resultSearch').fadeIn();
        var searchval = $('#search').val();
        if(searchval != "") {
            $('.dropdown-menu').fadeIn();
            $.ajax({
                type: 'POST',
                url: '/user/search',
                data: 'search=' + searchval,
                success: function (data) {
                    console.log(data);
                    $('.dropdown-menu').empty();
                    for (var i in data) {
                        var result = "<li><a tabindex='-1' href='" + data[i]['link'] + "'>"
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




});