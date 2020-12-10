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
      $curauth = (isset($_GET['author_name'])) ? get_user_by('slug', $author_name) : get_userdata(intval($author));
			$author_staff_role = get_the_author_meta('staff_role', $curauth->ID);
			?>

				<header class="page-header">
					<h1 class="page-title"><?php echo $curauth->display_name; ?></h1>
					<div class="bt-author-info-header">
						<span class="bt-author-staff-role">
							<?php
								echo $author_staff_role;
							?>
						</span>
					</div>
				</header><!-- .page-header -->
				<?php if (!strpos($curauth->display_name,'Bexley')) {
					echo "<style>.bt-author-bio{display: none;}</style>";
				} ?>
				<div class="bt-author-bio">
					<div class="bt-author-bio-img-wrapper">
						<img src="<?php
							$default_profile_url = "https://bexleytorch.org/wp-content/uploads/2020/10/Torch-Logo.png";
							if(isset($simple_local_avatars)) {
								$profile_url = $simple_local_avatars->get_simple_local_avatar_url($curauth->ID, 200);
								if(!$profile_url) {
									$profile_url = $default_profile_url;
								}
							}

							echo $profile_url;
						?>" alt="Profile Pic">
					</div>
					<p class="bt-author-bio-text">
						<?php
						if ($curauth->description) {
							echo $curauth->description;
						} else {
							if ($author_staff_role=='Staff Reporter') {
								echo $curauth->display_name . " is a junior at Bexley High School and a staff reporter on <i>The Torch</i>.";
							} else {
								echo $curauth->display_name . ' is a senior at Bexley High School and a <span id="staff-role-insert">' . $author_staff_role . '</span> on <i>The Torch</i>.';
							}
						}
						?>
					</p>
				</div>
				<div class="archive-content-wrapper clearfix">
					<?php
                    /* Start the Loop */
                    while (have_posts()) : the_post();
                        /*
                        * Include the Post-Format-specific template for the content.
                        * If you want to override this in a child theme, then include a file
                        * called content-___.php (where ___ is the Post Format name) and that will be used instead.
                        */
                        get_template_part( 'template-parts/content', get_post_format() );

                    endwhile;

                    the_posts_pagination();
                    ?>
				</div><!-- .archive-content-wrapper -->

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
eggnews_sidebar();
get_footer();
