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
         home = $('#home').val();
        if(home==1){
            link = $(this).attr('href');
            if(link!="https://linarte.com.co/productos"){
                e.preventDefault();
                mover = $(link).offset().top;
                $( window ).scrollTo( mover  , {
                    duration: 1000
                });
                return false;
            }
        }        
        // type = $('#type').val();
        // if (type=="post"){
        //     nam = $(this).attr('name');             
        //     if(nam == "habitaciones"){                
        //         e.preventDefault();
        //         const myModal = new bootstrap.Modal('#habitaciones');
        //         myModal.show();
        //     }
        // }
    });
    $(".wp3dcarousellightbox-1").click(function(e) {        
        var principal = $(this).closest('li').css("z-index");
        if(principal==6){
            var nam = $(this).closest('li').find('.wonderplugin3dcarousel-hoveroverlay-title').text();
            switch (nam) {
                case "ZONA SOCIAL COLOMBIA":                    
                    myModal = new bootstrap.Modal('#zona-social-colombia');
                break;
                case "HABITACIÓN ITALIA":                    
                    myModal = new bootstrap.Modal('#habitacion-italia');
                break;
                case "HABITACIÓN ESPAÑA":                    
                    myModal = new bootstrap.Modal('#habitacion-espana');
                break;
                case "HABITACIÓN MÉXICO":                    
                    myModal = new bootstrap.Modal('#habitacion-mexico');
                break;
                case "HABITACIÓN USA":                    
                    myModal = new bootstrap.Modal('#habitacion-usa');
                break;
                case "ESPACIOS ADICIONALES":                    
                    myModal = new bootstrap.Modal('#espacios-adicionales');
                break;
            }
            myModal.show();
        }
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