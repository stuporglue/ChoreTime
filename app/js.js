$(document).ready(function(){
    $('form').on('submit',function(e){
        var f = $(e.target);
        var url = './chores.php?' + f.serialize();
        $.getJSON(url,function(success){
            if(success.done){
                f.find('.notdone').addClass('done').removeClass('notdone');
            }else if(success.done === false){
                f.find('.done').addClass('notdone').removeClass('done');
            }

            $('.time_left.' + f.find('[name=username]').val()).html(success.timeleft);
        },function(failure){
            console.log(failure);
        });
        return false;
    });

    $('.lockbutton').on('click',function(e){
        var username = $(e.target).closest('td').data('username');
        var url = 'lock.php?user=' + username;
        $.get(url);
    });
});
