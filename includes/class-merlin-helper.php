<?php
/**
 * Merlin WP
 * Better WordPress Theme Onboarding
 *
 * @package   Merlin WP
 * @version   @@pkg.version
 * @link      https://merlinwp.com/
 * @author    Richard Tabor, from ThemeBeans.com
 * @copyright Copyright (c) 2017, Merlin WP of Inventionn LLC
 * @license   Licensed GPLv3 for open source use
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Merlin_Helper' ) ) :

	/**
	 * The wizard for the wizard.
	 */
	class Merlin_Helper {

		/**
		 * The class constructor.
		 */
		public function __construct() {}

		/**
		 * Output the helper.
		 *
		 * @param string $current_step  The current step.
		 */
		public function helper_wizard( $current_step ) {
		?>

			<div class="merlin__helper">

				<?php if ( 'welcome' == $current_step ) : ?>
					<div class="from-me with-second-message">
						<p>Welcome to Merlin! If you need anything,<br>get in touch via <a href="mailto:hi@merlinwp.com">hi@merlinwp.com</a></p> 
					</div>
					<div class="from-me is-third-message">
						<p>BTW, you're seeing this because you have 'dev_mode' set to 'true' in your config file. Don't forget to turn it off when you're done. :)</p>
					</div>
					<div class="chat-bubble"><div class="loading"><div class="dot one"></div><div class="dot two"></div><div class="dot three"></div></div><div class="tail"></div></div>
				<?php endif; ?>

				<?php if ( 'child' == $current_step ) : ?>
					<div class="from-me">
						<p>Don't forget, there are <a href="" target="_blank">available filters</a> so you may modify the generated child functions.php and style.css files. Pretty magical 'eh!?</p>
					</div>
				<?php endif; ?>

			</div>
		<?php
		}
	}

endif;
