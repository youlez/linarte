<?php
if (have_posts()) the_post();
$post_name = get_post_field('post_name', get_post());
get_header();
?>
<div class="entradas mt-4">
    <div class="col-lg-8 offset-lg-2 col-10 offset-1">
        <h1 class="titulo text-black text-center"><?php the_title(); ?></h1>
        <?php
        if (get_post_type() == "post") {
        ?>
            <div class="galeria my-4 animacion fadeIn">
                <?php
                echo do_shortcode(get_post_meta(get_the_id(), 'galeria', true));
                ?>
            </div>
            <div class="animacion fadeIn text-black mt-4 fs-5">
                <h2 class="mb-3 fw-bold">Descripción</h2>
                <?php the_content(); ?>
            </div>
            <a class="reservar-inicio text-decoration-none fs-1" target="_blank" href="https://wa.me/+57<?php echo get_option('telefono_info'); ?>?text=<?php echo get_option('info_whatsapp'); ?>">RESERVA AHORA!</a>
        <?php
        } else {
            $post_thumbnail_id = get_post_thumbnail_id();
            $post_thumbnail_url = wp_get_attachment_url($post_thumbnail_id);
        ?>
            <div class="row justify-content-center mt-4 pt-4">
                <div class="col-6 ">
                    <img src="<?php echo $post_thumbnail_url; ?>" class="img-fluid mb-4 animacion fadeIn" alt="...">
                    <div class="text-center text-black animacion fadeIn mb-4">
                        <?php the_content(); ?>
                        <b class="fs-3">
                            <?php
                            echo do_shortcode(get_post_meta(get_the_id(), 'precio', true));
                            ?>
                        </b>
                    </div>
                    <div class="row justify-content-center">
                        <a target="_blank" href="https://wa.me/+57<?php echo get_option('telefono_info'); ?>?text=<?php echo get_option('info_whatsapp_producto') . " " . get_bloginfo('url') . "/" . get_post_field('post_name', get_post()); ?>" class="comprar btn-vermas p-3 animacion fadeIn text-white fw-bold text-decoration-none">
                            COMPRAR
                        </a>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</div>
<div class="modal-habitaciones modal animacion fadeIn fast" id="habitaciones" tabindex="-1" aria-labelledby="habitacionesLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="titulo text-black" id="habitacionesLabel">HABITACIONES</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php
                echo do_shortcode("[wonderplugin_3dcarousel id=1]");
                ?>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();
