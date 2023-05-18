<?php
add_theme_support('post-thumbnails');

register_nav_menus(array(
    'top'    => __('Top Menu', 'omega'),
));

function cargar_scripts()
{
?>
    <script type="text/javascript">
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        var aulaurl = 'https://creaencasa.idartes.gov.co/ws-aula/index.php/';
    </script>
<?php
    wp_register_script('bootstrap', get_template_directory_uri() . '/node_modules/bootstrap/dist/js/bootstrap.min.js', array('jquery'), false, false);
    wp_register_script('linarte-jquery', get_template_directory_uri() . '/js/linarte.js', array('jquery'), "2022.03.18.06", true);
    wp_register_script('jquery-scrollto', get_template_directory_uri() . '/node_modules/jquery.scrollto/jquery.scrollTo.min.js', array('jquery'), false, false);
    wp_register_script('bootstrap-bundle', get_template_directory_uri() . '/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', array('jquery'), false, false);

    /*if(!is_home())
        wp_enqueue_script( 'bootstrap' );*/
    wp_enqueue_script('linarte-jquery');
    wp_enqueue_script('jquery-scrollto');
    wp_enqueue_script('bootstrap-bundle');
}

add_action('wp_enqueue_scripts', 'cargar_scripts');

function cargar_estilos()
{
    wp_register_style('estilo-principal', get_template_directory_uri() . '/style.css', array(), '2022.03.18.06', 'all');
    wp_register_style('bootstrap', get_template_directory_uri() . '/node_modules/bootstrap/dist/css/bootstrap.css', array(), false, 'all');
    wp_register_style('bootstrap-icons', get_template_directory_uri() . '/node_modules/bootstrap-icons/font/bootstrap-icons.css', array(), false, 'all');

    wp_enqueue_style('estilo-principal');
    wp_enqueue_style('bootstrap');
    wp_enqueue_style('bootstrap-icons');
}

add_action('wp_enqueue_scripts', 'cargar_estilos');


function crear_entrada($entrada, $nombre, $genero)
{
    if ($genero == 'f') {
        $varios = 'Todas';
        $crear  = 'Nueva';
        $ningun = 'ninguna';
    } else {
        $varios = 'Todos';
        $crear  = 'Nuevo';
        $ningun = 'ninguno';
    }

    $vocales = "aeiouAEIOU";
    if (is_numeric(strpos($vocales, substr($nombre, strlen($nombre) - 1, strlen($nombre) - 1))) == false) {
        if ($genero == 'f') {
            $plural = 'as ';
        } else {
            $plural = 'es ';
        }
    } else {
        $plural = 's ';
    }

    $labels = array(
        'name' => __($nombre . $plural),
        'singular_name' => __($nombre),
        'search_items' =>  __('Buscar ' . strtolower($nombre) . $plural),
        'all_items' => __($varios),
        'add_new' => __('Añadir ' . strtolower($crear), strtolower($nombre)),
        'add_new_item' => __('Añadir ' . strtolower($crear) . $plural . strtolower($nombre) . $plural),
        'edit_item' => __('Editar ' . strtolower($nombre)),
        'new_item' => __($crear . ' ' . strtolower($nombre)),
        'view_item' => __('Ver ' . strtolower($nombre)),
        'not_found' =>  __('No se ha encontrado ' . $ningun . ' ' . strtolower($nombre)),
        'not_found_in_trash' => __('No se han encontrado ' . $ningun . ' ' . strtolower($nombre) . ' en la papelera'),
        'parent_item_colon' => ''
    );
    register_post_type(
        $entrada,
        array(
            'label' => __(strtolower($nombre)),
            'labels' => $labels,
            'public' => true,
            'can_export' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            '_builtin' => false,
            'capability_type' => 'post',
            'hierarchical' => true,
            'rewrite' => array('with_front' => true),
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'custom-fields', 'page-attributes'),
            'show_in_nav_menus' => true,
            'menu_icon' => 'dashicons-admin-post',
            'map_meta_cap' => true
        )
    );
}
function crear_entradas()
{
    crear_entrada('productos', 'Producto', 'm');
}
add_action('init', 'crear_entradas');

add_filter('admin_init', 'register_my_general_settings_fields');

function register_my_general_settings_fields()
{
    register_setting('general', 'correo_info', 'esc_attr');
    add_settings_field('correo_info', '<label for="correo_info">' . __('Correo de Información', 'correo_info') . '</label>', 'general_settings_correo_info_html', 'general');

    register_setting('general', 'telefono_info', 'esc_attr');
    add_settings_field('telefono_info', '<label for="telefono_info">' . __('Telefono', 'telefono_info') . '</label>', 'general_settings_telefono_info_html', 'general');

    register_setting('general', 'direccion_info', 'esc_attr');
    add_settings_field('direccion_info', '<label for="direccion_info">' . __('Direccion', 'direccion_info') . '</label>', 'general_settings_direccion_info_html', 'general');

    register_setting('general', 'municipio_info', 'esc_attr');
    add_settings_field('municipio_info', '<label for="municipio_info">' . __('Municipio', 'municipio_info') . '</label>', 'general_settings_municipio_info_html', 'general');

    register_setting('general', 'info_whatsapp', 'esc_attr');
    add_settings_field('info_whatsapp', '<label for="info_whatsapp">' . __('Texto WhatsApp', 'info_whatsapp') . '</label>', 'general_settings_info_whatsapp_html', 'general');

    register_setting('general', 'info_whatsapp_producto', 'esc_attr');
    add_settings_field('info_whatsapp_producto', '<label for="info_whatsapp_producto">' . __('Texto WhatsApp para Productos', 'info_whatsapp_producto') . '</label>', 'general_settings_info_whatsapp_producto_html', 'general');
}

function general_settings_correo_info_html()
{
    $value = get_option('correo_info', '');
    echo '<input type="text" id="correo_info" name="correo_info" value="' . $value . '" class="regular-text ltr"/>';
}

function general_settings_telefono_info_html()
{
    $value = get_option('telefono_info', '');
    echo '<input type="text" id="telefono_info" name="telefono_info" value="' . $value . '" class="regular-text ltr" />';
}

function general_settings_direccion_info_html()
{
    $value = get_option('direccion_info', '');
    echo '<input type="text" id="direccion_info" name="direccion_info" value="' . $value . '" class="regular-text ltr" />';
}

function general_settings_municipio_info_html()
{
    $value = get_option('municipio_info', '');
    echo '<input type="text" id="municipio_info" name="municipio_info" value="' . $value . '" class="regular-text ltr" />';
}

function general_settings_info_whatsapp_html()
{
    $value = get_option('info_whatsapp', '');
    echo '<textarea id="info_whatsapp" name="info_whatsapp" cols="100" rows="5">' . $value . '</textarea>';
}

function general_settings_info_whatsapp_producto_html()
{
    $value = get_option('info_whatsapp_producto', '');
    echo '<textarea id="info_whatsapp_producto" name="info_whatsapp_producto" cols="100" rows="5">' . $value . '</textarea>';
}

add_filter('kdmfi_featured_images', function ($featured_images) {
    // Add featured-image-2 to pages and posts
    $args_1 = array(
        'id' => 'featured-image-2',
        'desc' => 'Segunda Imagen',
        'label_name' => 'Segunda Imagen',
        'label_set' => 'Establecer Imagen',
        'label_remove' => 'Remover Imagen',
        'label_use' => 'Establecer Imagen',
        'post_type' => array('post'),
    );

    // Add the featured images to the array, so that you are not overwriting images that maybe are created in other filter calls
    $featured_images[] = $args_1;

    // Important! Return all featured images
    return $featured_images;
});
function lt_html_excerpt($text)
{ // Fakes an excerpt if needed
    global $post;
    if ('' == $text) {
        $text = get_the_content('');
        $text = apply_filters('the_content', $text);
        $text = str_replace('\]\]\>', ']]>', $text);
        /*just add all the tags you want to appear in the excerpt --
        be sure there are no white spaces in the string of allowed tags */
        $text = strip_tags($text, '<p><br><b><a><em><strong>');
        /* you can also change the length of the excerpt here, if you want */
        $excerpt_length = 25;
        $words = explode(' ', $text, $excerpt_length + 1);
        if (count($words) > $excerpt_length) {
            array_pop($words);
            array_push($words, '[...]');
            $text = implode(' ', $words);
        }
    }
    return $text;
}
/* remove the default filter */
remove_filter('get_the_excerpt', 'wp_trim_excerpt');

/* now, add your own filter */
add_filter('get_the_excerpt', 'lt_html_excerpt');
