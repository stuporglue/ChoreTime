$(document).ready(function(){
    $('form').on('submit',function(e){
        console.log("Submit via post!");
        return false;
    });
});
