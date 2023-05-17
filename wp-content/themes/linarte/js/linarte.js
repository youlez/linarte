jQuery(document).ready(function($){    
    doAnimations();
    function doAnimations(){
        $(".animacion").each(function(i) {
            var offset = $(window).scrollTop() + ($(window).height() * 0.9);
            if ($(this).offset().top < offset) {
                $(this).addClass('running');
            }
        });
    }
    $(window).on('scroll', doAnimations);
    $(document).on( "click", "#menu a", function(e) {
        e.preventDefault();
        link = $(this).attr('href');
            mover = $(link).offset().top;
            $( window ).scrollTo( mover  , {
                duration: 1000
            });
            return false;
    });
    //cuadros();
});

jQuery(window).on("resize", function(){ 
    //cuadros();
});

function cuadros(){
    jQuery(".desc_rigth").each(function(i) {
        var tam_desc = this.offsetHeight;
        jQuery(this).closest('section').find(".img_left").height(tam_desc + 20);
    });
    jQuery(".desc_left").each(function(i) {
        var tam_desc = this.offsetHeight;
        jQuery(this).closest('section').find(".img_rigth").height(tam_desc + 20);
    });
    jQuery(".img_deg_rigth").each(function(i) {
        var tam_desc = jQuery(this).find(".desc_deg div")[0].offsetHeight;
        jQuery(this).height(tam_desc + 20);
    });
    jQuery(".img_deg_left").each(function(i) {
        var tam_desc = jQuery(this).find(".desc_deg div")[0].offsetHeight;
        jQuery(this).height(tam_desc + 20);
    });
}