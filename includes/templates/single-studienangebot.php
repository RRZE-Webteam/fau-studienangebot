<?php
/**
 * Template Name: Studienangebot Template
 */

get_header(); ?>

<?php while(have_posts() ) : the_post();?>

	<?php get_template_part('hero', 'small'); ?>

	<div id="content">
		<div class="container">

			<div class="row">
				
				<div class="span12">
					<h2><?php the_field('headline'); ?></h2>
					<?php if( get_field('abstract') != ''): ?>
						<h3 class="abstract"><?php the_field('abstract'); ?></h3>
					<?php endif; ?>
					
					<?php get_template_part('sidebar', 'inline'); ?>
					
					<?php the_content(); ?>
				</div>
				
			</div>

		</div>
	</div>
	
<?php endwhile; ?>
          
<?php wp_reset_query(); ?>
<?php get_footer(); ?>