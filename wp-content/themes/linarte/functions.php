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

function crear_taxonomia($taxonomia, $nombre, $genero, $posts)
{
    if ($genero == 'f') {
        $varios = 'Todas';
        $crear  = 'nueva';
        $ningun = 'ninguna';
    } else {
        $varios = 'Todos';
        $crear  = 'nuevo';
        $ningun = 'ninguno';
    }

    $vocales = "aeiouAEIOU";
    if (is_numeric(strpos($vocales, substr($nombre, strlen($nombre) - 1, strlen($nombre) - 1))) == false) {
        $plural = 'es ';
    } else {
        $plural = 's ';
    }

    $labels = array(
        'name' => __($nombre . $plural),
        'singular_name' => __($nombre),
        'search_items' =>  __('Buscar ' . strtolower($nombre) . $plural),
        'all_items' => __($varios),
        'parent_item' => __($nombre . ' padre'),
        'parent_item_colon' => __($nombre . ' padre:'),
        'edit_item' => __('Editar ' . strtolower($nombre)),
        'update_item' => __('Actualizar ' . strtolower($nombre)),
        'add_new' => _x('Añadir ' . $crear, strtolower($nombre)),
        'add_new_item' => __('Añadir ' . $crear . ' ' . strtolower($nombre)),
        'not_found' =>  __('No se ha encontrado ' . $ningun . ' ' . strtolower($nombre)),
        'menu_name' => __($nombre . $plural)
    );
    register_taxonomy(
        $taxonomia,
        $posts, // Tipos de Post a los que asociaremos la taxonomía
        array(
            'hierarchical' => true, // True para taxonomías del tipo "Categoría" y false para el tipo 
            'labels' => $labels, // La variable con las traducciones de las etiquetas
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => $nombre),
        )
    );
}
function crear_taxonomias()
{
    crear_taxonomia('categoria', 'Categoria', 'f', array('page'));
}
//add_action('init', 'crear_taxonomias', 0);

add_filter('admin_init', 'register_my_general_settings_fields');

function register_my_general_settings_fields()
{
    register_setting('general', 'correo_info', 'esc_attr');
    add_settings_field('correo_info', '<label for="correo_info">' . __('Correo de Información', 'correo_info') . '</label>', 'general_settings_correo_info_html', 'general');

    register_setting('general', 'telefono_info', 'esc_attr');
    add_settings_field('telefono_info', '<label for="telefono_info">' . __('Telefono de Información', 'telefono_info') . '</label>', 'general_settings_telefono_info_html', 'general');
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
