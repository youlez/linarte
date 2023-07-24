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
            <div class="col-4 col-lg-2">
                <img src="<?php bloginfo('template_url'); ?>/img/logo.jpeg" class="col-12 col-lg-7 img-fluid logo" alt="">
            </div>
            <div class="offset-1 col-2 offset-lg-0 col-lg-8 p-0 d-flex justify-content-center align-items-center">
                <nav class="navbar navbar-expand-lg bg-body-tertiary d-lg-none">
                    <div class="container-fluid">
                        <button class="navbar-toggler border border-dark" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-controls="menu" aria-expanded="false" id="boton-menu">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                    </div>
                </nav>
                <ul class="nav d-none d-lg-flex" id="menu">
                    <li class="nav-item">
                        <a class="nav-link" name="inicio" href="<?php echo is_home() ? "#inicio" : get_bloginfo('url'); ?>">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" name="ubicacion" href="<?php echo is_home() ? "#ubicacion" : get_bloginfo('url') . "/#ubicacion"; ?>">Ubicación</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" name="espacios" href="<?php echo is_home() ? "#espacios" : get_bloginfo('url') . "/#espacios"; ?>">Espacios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" name="habitaciones" href="<?php echo is_home() ? "#habitaciones" : get_bloginfo('url') . "/#habitaciones"; ?>">Habitaciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" name="productos" href="<?php echo get_bloginfo('url'); ?>/productos">Productos</a>
                    </li>
                </ul>
            </div>
            <div class="col-5 col-lg-2 d-flex justify-content-end align-items-center p-0">
                <div id="contactos" class="ms-2 ms-lg-0">
                    <a target="_blank" href="https://wa.me/+57<?php echo get_option('telefono_info'); ?>?text=<?php echo get_option('info_whatsapp'); ?>"><i class="bi bi-whatsapp pe-2"></i>+57 <?php echo get_option('telefono_info'); ?></a>
                    <a href="mailto:<?php echo get_option('correo_info'); ?>"><i class="bi bi-envelope pe-2"></i><?php echo get_option('correo_info'); ?></a>
                </div>
            </div>
            <div class="d-block d-lg-none col-12">
                <div class="collapse navbar-collapse" id="menu">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" name="inicio" href="<?php echo is_home() ? "#inicio" : get_bloginfo('url'); ?>">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" name="ubicacion" href="<?php echo is_home() ? "#ubicacion" : get_bloginfo('url') . "/#ubicacion"; ?>">Ubicación</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" name="espacios" href="<?php echo is_home() ? "#espacios" : get_bloginfo('url') . "/#espacios"; ?>">Espacios</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" name="habitaciones" href="<?php echo is_home() ? "#habitaciones" : get_bloginfo('url') . "/#habitaciones"; ?>">Habitaciones</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" name="productos" href="<?php echo get_bloginfo('url'); ?>/productos">Productos</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>
    <input type="hidden" id="home" value="<?php echo is_home() ? "1" : "0"; ?>">
    <input type="hidden" id="type" value="<?php echo get_post_type(); ?>">