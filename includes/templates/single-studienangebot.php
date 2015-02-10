<?php
/**
 * Template Name: Studienangebot Template
 */

get_header(); ?>

<?php while(have_posts() ) : the_post();?>

	<div id="content">
		<div class="container">				
            <div>					
                <?php printf('<h3>%s</h3>', $post->post_title);	?>				
                <?php the_content(); ?>
            </div>		
		</div>
	</div>
	
<?php endwhile; ?>
          
<?php wp_reset_query(); ?>
<?php get_footer(); ?>