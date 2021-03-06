<?php

/**
 * @file
 * Default theme implementation to display a single Drupal page.
 *
 * Available variables:
 *
 * General utility variables:
 * - $base_path: The base URL path of the Drupal installation. At the very
 *   least, this will always default to /.
 * - $directory: The directory the template is located in, e.g. modules/system
 *   or themes/bartik.
 * - $is_front: TRUE if the current page is the front page.
 * - $logged_in: TRUE if the user is registered and signed in.
 * - $is_admin: TRUE if the user has permission to access administration pages.
 *
 * Site identity:
 * - $front_page: The URL of the front page. Use this instead of $base_path,
 *   when linking to the front page. This includes the language domain or
 *   prefix.
 * - $logo: The path to the logo image, as defined in theme configuration.
 * - $site_name: The name of the site, empty when display has been disabled
 *   in theme settings.
 * - $site_slogan: The slogan of the site, empty when display has been disabled
 *   in theme settings.
 *
 * Navigation:
 * - $main_menu (array): An array containing the Main menu links for the
 *   site, if they have been configured.
 * - $secondary_menu (array): An array containing the Secondary menu links for
 *   the site, if they have been configured.
 * - $breadcrumb: The breadcrumb trail for the current page.
 *
 * Page content (in order of occurrence in the default page.tpl.php):
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title: The page title, for use in the actual HTML content.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 * - $messages: HTML for status and error messages. Should be displayed
 *   prominently.
 * - $tabs (array): Tabs linking to any sub-pages beneath the current page
 *   (e.g., the view and edit tabs when displaying a node).
 * - $action_links (array): Actions local to the page, such as 'Add menu' on the
 *   menu administration interface.
 * - $feed_icons: A string of all feed icons for the current page.
 * - $node: The node object, if there is an automatically-loaded node
 *   associated with the page, and the node ID is the second argument
 *   in the page's path (e.g. node/12345 and node/12345/revisions, but not
 *   comment/reply/12345).
 *
 * Regions:
 * - $page['help']: Dynamic help text, mostly for admin pages.
 * - $page['highlighted']: Items for the highlighted content region.
 * - $page['content']: The main content of the current page.
 * - $page['sidebar_first']: Items for the first sidebar.
 * - $page['sidebar_second']: Items for the second sidebar.
 * - $page['header']: Items for the header region.
 * - $page['footer']: Items for the footer region.
 *
 * @see template_preprocess()
 * @see template_preprocess_page()
 * @see template_process()
 */
?>
<div id="page-wrapper">
  <?php if ($site_name): ?>
    <?php if ($title): ?>
      <div id="site-name" class="element-invisible"><strong>
        <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home"><span><?php print $site_name; ?></span></a>
      </strong></div>
    <?php else: /* Use h1 when the content title is empty */ ?>
      <h1 id="site-name" class="element-invisible">
        <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home"><span><?php print $site_name; ?></span></a>
      </h1>
    <?php endif; ?>
  <?php endif; ?>

  <div id="nav-and-release-book-wrapper">
    <?php print theme('bookcrossing_main_menu', array('visible' => 3, 'links' => $main_menu, 'attributes' => array('id' => 'main-menu', 'class' => array('links', 'clearfix')), 'heading' => '')); ?>

    <?php if ($logged_in) : ?>
      <div id="release-button">
        <a href="<?php print $base_path; ?>release-book"><?php print t('Release book'); ?></a>
      </div><!-- /#release-button -->
    <?php endif; ?>

    <div id="login-wrapper">
      <?php if ($logged_in) : ?>
        <div class="my-name"><?php print /*l($user->name, 'user', array('attributes' => array('class' => array('my-profile')))) .*/  '<span>' . $user->name . '</span>' . l(t('(log out)'), 'user/logout', array('attributes' => array('class' => array('logout-link')))); ?></div>
        <div class="my-shelf"><?php print l(t('My shelf'), 'user/' . $user->uid); ?></div>
      <?php else : ?>
        <?php print fboauth_action_display('connect'); ?>
      <?php endif; ?>
    </div><!-- /#login -->
  </div><!-- /#navigation-and-release-book -->

  <div id="header-wrapper">
    <?php if ($logo): ?>
      <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home" id="logo">
        <img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" />
      </a>
    <?php endif; ?>

    <?php print render($page['header_sidebar_first']); ?>
    <?php print render($page['header_sidebar_second']); ?>
    <div style="clear:left;"></div>
    <div id="help-link"><?php print l(t('Help'), variable_get('bookcrossing_help_link', '')); ?></div>

    <div id="fb-wrapper">
      <div class="fb-like" data-href="http://www.facebook.com/bookcrossing.by" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false" data-font="tahoma"></div>
    </div>
    <div id="github-wrapper">

      <iframe src="/github-btn.html?user=bookcrossing&repo=core&type=fork&count=true" allowtransparency="true" frameborder="0" scrolling="0" width="130" height="20"></iframe>

    </div>
  </div><!-- /#header-wrapper -->


  <?php print render($page['under_content']); ?>

  <div id="content-wrapper">
    <?php print $messages; ?>
    <?php print render($page['sidebar_first']); ?>
    <?php print render($page['content']); ?>
    <?php print render($page['sidebar_second']); ?>
    <div style="clear:left;"></div>
  </div>

  <?php print render($page['after_content']); ?>

  <div id="footer-wrapper">
    <?php print render($page['footer_menu']); ?>
    <?php print render($page['footer_text']); ?>

    <div class="ira-young"><?php print '<div class="design-by">' . t('Design by') . '</div>' . $ira_yohng; ?></div>
  </div><!-- /#footer-wrapper -->
</div><!-- /#page-wrapper -->
<div id="back-top" style="display:none;">
  <script type="text/javascript">
		/*<![CDATA[*/
    (function ($) {
			$(document).ready(function(){
				$("#back-top").hide();
				$(function () {
					$(window).scroll(function () {
						if ($(this).scrollTop() > 50) {
							$('#back-top').fadeIn();
						} else {
							$('#back-top').fadeOut();
						}
					});
					$('#back-top a').click(function () {
						$('body,html').animate({
							scrollTop: 0
						}, 300);
						return false;
					});
				});
			});
    })(jQuery);
		/*]]>*/
		</script>
  <a href="#"><?php print t('back to top'); ?></a>
</div><!-- /#back-top -->
<div id="banners-wrapper">
    <?php print render($page['banners_footer']); ?>
</div>
<div id="debug-wrapper">
    <?php print render($page['debug_n_scripts']); ?>
</div>