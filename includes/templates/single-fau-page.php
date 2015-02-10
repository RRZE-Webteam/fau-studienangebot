<?php
/**
 * Template Name: Studienangebot FAU Theme Template
 */
get_header();
?>

<?php while (have_posts()) : the_post(); ?>

    <?php get_template_part('hero', 'small'); ?>

    <section id="content" class="content-portal">
        <div class="container">

            <div class="row">
                <div class="span8">
                    <?php the_content(); ?>
                </div>
                <div class="span4">
                    <aside class="widget">
                        <?php dynamic_sidebar('sa-sidebar'); ?>
                    </aside>
                </div>
            </div>

        </div>
    </section>
<?php endwhile; ?>

<?php wp_reset_query(); ?>
<?php get_footer(); ?>