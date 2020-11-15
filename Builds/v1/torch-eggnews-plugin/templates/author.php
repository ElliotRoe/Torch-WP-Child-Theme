<?php
/**
 * The template for displaying author pages.
 *
 *
 * @package Bexley Torch
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php
						$teg_cat_id = get_query_var( 'cat' );
						if ( have_posts() ) :
			?>
			<?php
      $curauth = (isset($_GET['author_name'])) ? get_user_by('slug', $author_name) : get_userdata(intval($author));
      ?>

				<header class="page-header">
					<h1 class="page-title"><?php echo $curauth->display_name; ?></h1>
					<div class="bt-author-info-header">
						<span class="bt-author-staff-role"><?php echo the_author_meta( 'staff_role', $curauth->ID )?></span>
						<?php // TODO: Add view functionality?>
						<span class="bt-author-views">Views: NA</span></div>
				</header><!-- .page-header -->
				<div class="bt-author-bio">
				<?php echo $curauth->description ?>
				</div>
				<div class="archive-content-wrapper clearfix">
					<?php
					/* Start the Loop */
					while ( have_posts() ) : the_post();
						/*
						* Include the Post-Format-specific template for the content.
						* If you want to override this in a child theme, then include a file
						* called content-___.php (where ___ is the Post Format name) and that will be used instead.
						*/
						get_template_part( WP_CONTENT_DIR . 'themes/eggnews/template-parts/content', get_post_format());

					endwhile;

					the_posts_pagination();
					?>
				</div><!-- .archive-content-wrapper -->
				<?php
			else :

				get_template_part( WP_CONTENT_DIR . 'themes/eggnews/template-parts/content', 'none' );

			endif;
			?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
eggnews_sidebar();
get_footer();
