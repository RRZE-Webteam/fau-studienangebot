<?php
/**
 * Template Name: Studienangebot FAU Theme Template
 */
get_header();
?>

<?php while (have_posts()) : the_post(); ?>

    <?php get_template_part('template-parts/hero', 'small'); ?>

    <section id="content" class="content-portal">
        <div class="container">

            <div class="row">
                <div class="portalpage-content">
                    <?php the_content(); ?>
                </div>
                <div class="portalpage-sidebar">
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