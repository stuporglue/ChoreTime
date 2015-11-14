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

            if(success.timeleft !== undefined){
                $('.time_left.' + f.find('[name=username]').val()).html(success.timeleft);
            }

            if(success.spending !== undefined){
                $('.spending.' + f.find('[name=username]').val()).html(success.spending);
            }
            if(success.tithing !== undefined){
                $('.tithing.' + f.find('[name=username]').val()).html(success.tithing);
            }
            if(success.savings !== undefined){
                $('.savings.' + f.find('[name=username]').val()).html(success.savings);
            }
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

    if($('body.index')){
        var now = new Date();
        var start = new Date(now.getFullYear(),0,0);
        var diff = now - start;
        oneDay = 1000 * 60 * 60 * 24;
        startDay = Math.floor(diff / oneDay);

        setInterval(function(){
            var now = new Date();
            var start = new Date(now.getFullYear(),0,0);
            var diff = now - start;
            curDay = Math.floor(diff / oneDay);
            
            if(curDay != startDay){
                document.location = document.location;
            }

        },1000*60*15); // Check if we need to refresh every 15 minutes
    }
});
