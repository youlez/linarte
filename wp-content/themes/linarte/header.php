<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>

<body>
    <header class="col-12">
        <div class="row m-2">
            <div class="col-2">
                <img src="<?php bloginfo('template_url'); ?>/img/logo.jpeg" class="col-7 img-fluid logo" alt="">
            </div>
            <div class="col-8 d-flex justify-content-center align-items-center">
                <ul class="nav" id="menu">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo is_home() ? "#inicio" : get_bloginfo('url'); ?>">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo is_home() ? "#ubicacion" : get_bloginfo('url') . "/#ubicacion"; ?>">Ubicación</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo is_home() ? "#espacios" : get_bloginfo('url') . "/#espacios"; ?>">Espacios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo is_home() ? "#habitaciones" : get_bloginfo('url') . "/#habitaciones"; ?>">Habitaciones</a>
                    </li>
                </ul>
            </div>
            <div class="col-2 d-flex justify-content-end align-items-center">
                <div id="contactos">
                    <a target="_blank" href="https://wa.me/+57<?php echo get_option('telefono_info'); ?>?text=<?php echo get_option('info_whatsapp'); ?>"><i class="bi bi-whatsapp pe-2"></i>+57 <?php echo get_option('telefono_info'); ?></a>
                    <a href="mailto:<?php echo get_option('correo_info'); ?>"><i class="bi bi-envelope pe-2"></i><?php echo get_option('correo_info'); ?></a>
                </div>
            </div>
        </div>
    </header>