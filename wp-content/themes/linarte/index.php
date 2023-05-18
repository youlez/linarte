<?php get_header(); ?>
<?php
$args = array(
    'name'        => 'inicio',
    'post_type'   => 'page'
);
$query = get_posts($args);
if ($query) {

    $post_thumbnail_id = get_post_thumbnail_id($query[0]->ID);
    $post_thumbnail_url = wp_get_attachment_url($post_thumbnail_id);
?>
    <section style="background-image: url(<?php echo $post_thumbnail_url; ?>);" id="inicio" class="animacion fadeIn">
        <div class="contenido animacion bounceInRight delay">
            <div class="descripcion px-4 py-1 text-black fs-5">
                <p><?php echo $query[0]->post_content; ?></p>
            </div>
            <a class="reservar-inicio text-decoration-none fs-2" target="_blank" href="https://wa.me/+57<?php echo get_option('telefono_info'); ?>">RESERVA AHORA</a>
        </div>
        <a href="#" class="position-absolute bottom-0 text-decoration-none link-video p-2" data-bs-toggle="modal" data-bs-target="#video">
            <div class="row m-2">
                <div class="col-12 d-flex justify-content-center fs-2">
                    <i class="bi bi-file-play-fill"></i>
                </div>
                <div class="col-12 p-0 fs-5 fw-bold text-center">
                    Ver Video
                </div>
            </div>
        </a>
    </section>
<?php } ?>
<section id="ubicacion" class="col-12 row animacion fadeIn">
    <div class="col-8 offset-2 row">
        <div class="col-8 p-4 cuadro">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31814.19138512585!2d-74.47870620094629!3d4.634378201774834!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e3f6c28c552b6f1%3A0x3fee08ff0b692e24!2sLa%20Mesa%2C%20Cundinamarca!5e0!3m2!1ses!2sco!4v1684264680850!5m2!1ses!2sco" width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <div class="col-4 animacion bounceInRight delay px-5">
            <h1 class="fs-2 titulo mb-4">ENCUENTRANOS</h1>
            <p><b>Dirección:</b> <?php echo get_option('direccion_info'); ?></p>
            <p><b>Municipio:</b> <?php echo get_option('lugar_info'); ?></p>
            <p>
                <b>Teléfono:</b>
                <a class="link text-decoration-none" target="_blank" href="https://wa.me/+57<?php echo get_option('telefono_info'); ?>?text=<?php echo get_option('info_whatsapp'); ?>">
                    <?php echo get_option('telefono_info'); ?>
                </a>
            </p>
        </div>
    </div>
</section>
<section id="espacios" class="animacion fadeIn position-relative">
    <div style="background-image: url(<?php bloginfo('template_url'); ?>/img/piscina.jpg);" class="imagen position-absolute col-12">
    </div>
    <div class="col-8 offset-2 contenido">
        <h1 class="fs-2 titulo mb-4 text-center text-black">NUESTROS ESPACIOS</h1>
        <div class="row d-flex justify-content-center align-items-center">
            <div class="col-3 p-4 pb-0 row d-flex justify-content-center align-items-center animacion fadeIn delay">
                <img class="img-fluid col-7" src="<?php bloginfo('template_url'); ?>/img/espacios/habitaciones.png" alt="">
                <p class="mt-2 text-center fs-5 text-black">
                    4 Habitaciones con cama doble y con baño
                </p>
            </div>
            <div class="col-3 p-4 pb-0 row d-flex justify-content-center align-items-center animacion fadeIn" style="-webkit-animation-delay: 0.4s;	-moz-animation-delay: 0.4s;	animation-delay: 0.4s;">
                <img class="img-fluid col-7" src="<?php bloginfo('template_url'); ?>/img/espacios/sala.png" alt="">
                <p class="mt-2 text-center fs-5 text-black">
                    Sala con Sofacama
                </p>
            </div>
            <div class="col-3 p-4 pb-0 row d-flex justify-content-center align-items-center animacion fadeIn" style="-webkit-animation-delay: 0.5s;	-moz-animation-delay: 0.5s;	animation-delay: 0.5s;">
                <img class="img-fluid col-7" src="<?php bloginfo('template_url'); ?>/img/espacios/comedor.png" alt="">
                <p class="mt-2 text-center fs-5 text-black">
                    Comedor
                </p>
            </div>
            <div class="col-3 p-4 pb-0 row d-flex justify-content-center align-items-center animacion fadeIn" style="-webkit-animation-delay: 0.6s;	-moz-animation-delay: 0.6s;	animation-delay: 0.6s;">
                <img class="img-fluid col-7" src="<?php bloginfo('template_url'); ?>/img/espacios/cocina.png" alt="">
                <p class="mt-2 text-center fs-5 text-black">
                    Cocina
                </p>
            </div>
            <div class="col-3 p-4 pb-0 row d-flex justify-content-center align-items-center animacion fadeIn delay">
                <img class="img-fluid col-7" src="<?php bloginfo('template_url'); ?>/img/espacios/baño.png" alt="">
                <p class="mt-2 text-center fs-5 text-black">
                    Baño Social
                </p>
            </div>
            <div class="col-3 p-4 pb-0 row d-flex justify-content-center align-items-center animacion fadeIn" style="-webkit-animation-delay: 0.4s;	-moz-animation-delay: 0.4s;	animation-delay: 0.4s;">
                <img class="img-fluid col-7" src="<?php bloginfo('template_url'); ?>/img/espacios/jacuzzi.png" alt="">
                <p class="mt-2 text-center fs-5 text-black">
                    Jacuzzi
                </p>
            </div>
            <div class="col-3 p-4 pb-0 row d-flex justify-content-center align-items-center animacion fadeIn" style="-webkit-animation-delay: 0.5s;	-moz-animation-delay: 0.5s;	animation-delay: 0.5s;">
                <img class="img-fluid col-7" src="<?php bloginfo('template_url'); ?>/img/espacios/piscina.png" alt="">
                <p class="mt-2 text-center fs-5 text-black">
                    Acceso a piscina del hotel
                </p>
            </div>
            <div class="col-3 p-4 pb-0 row d-flex justify-content-center align-items-center animacion fadeIn" style="-webkit-animation-delay: 0.6s;	-moz-animation-delay: 0.6s;	animation-delay: 0.6s;">
                <img class="img-fluid col-7" src="<?php bloginfo('template_url'); ?>/img/espacios/quebrada.png" alt="">
                <p class="mt-2 text-center fs-5 text-black">
                    Acceso a quebrada del hotel
                </p>
            </div>
        </div>
    </div>
</section>
<section id="habitaciones">
    <div class="col-8 offset-2">
        <h1 class="fs-2 titulo mb-4 text-center">HABITACIONES</h1>
        <?php
        echo do_shortcode("[wonderplugin_3dcarousel id=1]");
        ?>
    </div>
</section>
<?php
$args = array(
    'post_type' => array('post'),
    'post_status' => 'publish',
    'orderby'  => 'date',
    'order' => 'DESC'
);
$query = new WP_Query($args);
if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        $post_thumbnail_id = get_post_thumbnail_id();
        $post_thumbnail_url = wp_get_attachment_url($post_thumbnail_id);
?>

        <div class="modal-habitaciones modal animacion fadeIn fast" id="<?php echo get_post_field('post_name', get_post()); ?>" tabindex="-1" aria-labelledby="<?php echo get_post_field('post_name', get_post()); ?>Label" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="titulo text-black" id="<?php echo get_post_field('post_name', get_post()); ?>Label"><?php echo get_the_title(); ?></h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6">
                                <div id="carousel<?php echo get_post_field('post_name', get_post()); ?>" class="carousel slide carousel-fade" data-bs-ride="carousel">
                                    <div class="carousel-indicators">
                                        <button type="button" data-bs-target="#carousel<?php echo get_post_field('post_name', get_post()); ?>" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                        <button type="button" data-bs-target="#carousel<?php echo get_post_field('post_name', get_post()); ?>" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                    </div>
                                    <div class="carousel-inner">
                                        <div class="carousel-item active">
                                            <img src="<?php echo $post_thumbnail_url; ?>" class="img-fluid" alt="...">
                                        </div>
                                        <div class="carousel-item imagen-segunda">
                                            <?php echo do_shortcode("[kdmfi_featured_image id='featured-image-2' size='full']"); ?>
                                        </div>
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?php echo get_post_field('post_name', get_post()); ?>" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Anterior</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carousel<?php echo get_post_field('post_name', get_post()); ?>" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Siguiente</span>
                                    </button>
                                </div>
                            </div>
                            <div class="descripcion col-6 position-relative">
                                <p><?php echo get_the_excerpt(); ?></p>
                                <a href="<?php bloginfo('url'); ?>/<?php echo get_post_field('post_name', get_post()); ?>" class="btn-vermas px-3 py-2 text-white fw-bold text-decoration-none position-absolute bottom-0">VER MAS</a>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <a class="reservar-inicio text-decoration-none fs-3" target="_blank" href="https://wa.me/+57<?php echo get_option('telefono_info'); ?>">RESERVA AHORA</a>
                    </div>
                </div>
            </div>
        </div>
<?php
    }
}
?>
<div class="modal-espacios-adicionales modal animacion fadeIn fast" id="espacios-adicionales" tabindex="-1" aria-labelledby="espacios-adicionalesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="titulo text-black" id="espacios-adicionalesLabel">ESPACIOS ADICIONALES</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body galeria">
                <?php
                echo do_shortcode('[responsive-slider id=88]');
                ?>
            </div>
        </div>
    </div>
</div>
<div class="modal-video modal animacion fadeIn fast" id="video" tabindex="-1" aria-labelledby="videoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <video controls="" autoplay="" name="media" height="500px" style="border-radius: 5px">
                <source src="<?php bloginfo('template_url'); ?>/video/video.mp4" type="video/mp4">
            </video>
        </div>
    </div>
</div>
</body>
<?php
get_footer();
