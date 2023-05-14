<?php
if (have_posts()) the_post();
$post_name = get_post_field('post_name', get_post());
get_header();
?>

<?php
get_footer();
