<?php
if (have_posts()) the_post();
$post_name = get_post_field('post_name', get_post());
get_header();
if ($post_name == "productos") {
?>
    <div class="productos mt-4">
        <div class="col-lg-8 offset-lg-2 col-10 offset-1">
            <h1 class="titulo text-black text-center"><?php the_title(); ?></h1>
            <div class="animacion fadeIn text-black my-4 fs-5">
                <?php the_content(); ?>
            </div>
            <div class="row m-0">
                <?php
                $args = array(
                    'post_type' => array('productos'),
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
                        <div class="col-12 col-lg-4">
                            <div class="card animacion fadeIn">
                                <div class="card-header d-flex justify-content-center align-items-center" style="height: 75px;">
                                    <h4 class="text-black text-center"><?php the_title(); ?></h4>
                                </div>
                                <div class="card-body" style="height: 400px;">
                                    <img src="<?php echo $post_thumbnail_url; ?>" class="img-fluid mb-4" alt="...">
                                    <div class="text-center text-black">
                                        <?php the_content(); ?>
                                        <b class="fs-3">
                                            <?php
                                            echo do_shortcode(get_post_meta(get_the_id(), 'precio', true));
                                            ?>
                                        </b>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="row justify-content-center">
                                        <a target="_blank" href="https://wa.me/+57<?php echo get_option('telefono_info'); ?>?text=<?php echo get_option('info_whatsapp_producto') . " " . get_bloginfo('url') . "/" . get_post_field('post_name', get_post()); ?>" class="comprar btn-vermas p-3 text-white fw-bold text-decoration-none">
                                            COMPRAR
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                } else {
                    ?>
                    <div class="text-center fs-5 mt-4 row m-0 justify-content-center">
                        <div class="cuadro comprar p-3 text-white fw-bold text-decoration-none">
                            AÚN NO HAY PRODUCTOS PARA VENDER, ESPÉRALOS PRONTO
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
<?php
}
get_footer();
