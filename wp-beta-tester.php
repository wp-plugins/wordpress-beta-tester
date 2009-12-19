<?php
/*
	Plugin Name: WordPress Beta Tester
	Plugin URI: http://wordpress.org/extend/plugins/wordpress-beta-tester/
	Description: Allows you to easily upgrade to Beta releases.
	Author: Peter Westwood
	Version: 0.81
	Author URI: http://blog.ftwr.co.uk/
 */

class wp_beta_tester {
	var $real_wp_version;
	
	function wp_beta_tester() {
		add_action('admin_init', array(&$this, 'action_admin_init'));
		add_action('admin_menu', array(&$this, 'action_admin_menu'));
		add_action('init', array(&$this, 'action_init'));
		add_action('admin_head-update-core.php', array(&$this, 'action_admin_head_update_core_php'));
		add_action('update_option_wp_beta_tester_stream', array(&$this, 'action_update_option_wp_beta_tester_stream'));
	}
	
	function action_admin_init() {
		register_setting( 'wp_beta_tester_options', 'wp_beta_tester_stream', array(&$this,'validate_setting') );
	}
	
	function action_admin_menu() {
		add_management_page(__('Beta Testing WordPress','wp-beta-tester'), __('Beta Testing','wp-beta-tester'), 'update_plugins', 'wp_beta_tester', array(&$this,'display_page'));
	}
	
	function action_init() {
		// Load our textdomain
		load_plugin_textdomain('wp-beta-tester', false , basename(dirname(__FILE__)).'/languages');
		//Remove the default verson check function so we can add our wrapper function which plays with the version number
		remove_action( 'wp_version_check', 'wp_version_check' );
		add_action( 'wp_version_check', array(&$this, 'action_wp_version_check') );
		remove_action( 'admin_init', '_maybe_update_core' );
		if (function_exists('_maybe_update_core')) {
			add_action( 'admin_init', array(&$this, 'action__maybe_update_core') );
		}
	}
	
	function action_admin_head_update_core_php() {
		//On the update page wp_version_check is called directly so we need to remangle the info by calling it again
		$this->action_wp_version_check();
	}
	
	function action_wp_version_check()
	{
		$this->mangle_wp_version();
		wp_version_check();
		$this->restore_wp_version();
		$this->validate_upgrade_info();
	}
	
	
	function action__maybe_update_core() {
		$this->mangle_wp_version();
		_maybe_update_core();
		$this->restore_wp_version();
		$this->validate_upgrade_info();
	}

	function action_update_option_wp_beta_tester_stream() {
		//Our option has changed so update the cached information pronto.
		do_action('wp_version_check');
	}
	
	function _get_preferred_from_update_core() {
		if (!function_exists('get_preferred_from_update_core') )
			require_once(ABSPATH . 'wp-admin/includes/update.php');

		//Validate that we have api data and if not get the normal data so we always have it.
		$preferred = get_preferred_from_update_core();
		if (false === $preferred) {
			wp_version_check();
			$preferred =  get_preferred_from_update_core();
		}
		return $preferred;
	}
	
	/**
	 * Validate the current upgrade info after we have tried to get a nightly version
	 * 
	 * If its not valid get the update info for the default version
	 */
	function validate_upgrade_info() {
		$preferred = $this->_get_preferred_from_update_core();
		$head = wp_remote_head($preferred->package);
		if ( '404' == wp_remote_retrieve_response_code($head) ) {
			wp_version_check();
		}
	}
	
	function mangle_wp_version(){
		global $wp_version;
		$this->real_wp_version = $wp_version;

		$stream = get_option('wp_beta_tester_stream','point');
		$preferred = $this->_get_preferred_from_update_core();

		switch ($stream) {
			case 'point':
				$wp_version = $preferred->current . '.0-wp-beta-tester';
				break;
			case 'unstable':
				$versions = explode('.', $preferred->current);
				$versions[1] += 1;
				if (10 == $versions[1]) {
					$versions[0] += 1;
					$versions[1] = 0;
				}
				
				$wp_version = $versions[0] . '.' . $versions[1] . '-wp-beta-tester';
				break;
		}
	}
	
	function restore_wp_version() {
		global $wp_version;
		$wp_version = $this->real_wp_version;
	}
	
	function validate_setting($setting) {
		if (!in_array($setting, array('point','unstable')))
		{
			$setting = 'point';
		}
		return $setting;
	}

	function display_page() {
		if (!current_user_can('update_plugins'))
		{
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		$preferred = $this->_get_preferred_from_update_core();

		?>
	<div class="wrap"><?php screen_icon(); ?>
		<h2><?php _e('Beta Testing WordPress','wp-beta-tester')?></h2>
		<div class="updated fade">
			<p><?php _e('<strong>Please note:</strong> Once you have switched your blog to one of these beta versions of software it will not always be possible to downgrade as the database structure maybe updated during the development of a major release.', 'wp-beta-tester'); ?></p>	
		</div>
			<?php if ('development' != $preferred->response) : ?>
		<div class="updated fade">
			<p><?php _e('<strong>Please note:</strong> There are no development builds of the beta stream you have choosen available so you will receieve normal update notifications.', 'wp-beta-tester'); ?></p>
		</div>
			<?php endif;?>
		<div>
			<p><?php echo sprintf(__(	'By their nature these releases are unstable and should not be used anyplace where your data is important. So please <a href="%1$s">backup your database</a> before upgrading to a test release. In order to hear about the latest beta releases your best bet is to watch the <a href="%2$s">development blog</a> and the <a href="%3$s">beta forum</a>','wp-beta-tester'),
										_x('http://codex.wordpress.org/Backing_Up_Your_Database', 'Url to database backup instructions', 'wp-beta-tester'),
										_x('http://wordpress.org/development/', 'Url to development blog','wp-beta-tester'),
										_x('http://wordpress.org/support/forum/12', 'Url to beta support forum', 'wp-beta-tester') ); ?></p>
			<p><?php echo sprintf(__(	'Thank you for helping in testing WordPress please <a href="%s">report any bugs you find</a>.', 'wp-beta-tester'),
										_x('http://core.trac.wordpress.org/newticket', 'Url to raise a new trac ticket', 'wp-beta-tester') ); ?></p>
	
			<p><?php _e('By default your WordPress install uses the stable update stream, to return to this please deactivate this plugin', 'wp-beta-tester'); ?></p>
			<form method="post" action="options.php"><?php settings_fields('wp_beta_tester_options'); ?>
			<fieldset><legend><?php _e('Please select the update stream you would like this blog to use:','wp-beta-tester')?></legend>
				<?php
				$stream = get_option('wp_beta_tester_stream','point');
				?>
			<table class="form-table">
				<tr>
					<th><label><input name="wp_beta_tester_stream"
						id="update-stream-point-nightlies" type="radio" value="point"
						class="tog" <?php checked('point', $stream); ?> /><?php _e('Point release nightlies','wp-beta-tester');?></label></th>
					<td><?php _e('This contains the work that is occuring on a branch in preperation for a x.x.x point release.  This should also be fairly stable but will be available before the branch is ready for beta.','wp-beta-tester'); ?></td>
				</tr>
				<tr>
					<th><label><input name="wp_beta_tester_stream"
						id="update-stream-bleeding-nightlies" type="radio" value="unstable"
						class="tog" <?php checked('unstable', $stream); ?> /><?php _e('Bleeding edge nightlies','wp-beta-tester');?></label></th>
					<td><?php _e('This is the bleeding edge development code which may be unstable at times. <em>Only use this if you really know what you are doing</em>.','wp-beta-tester'); ?></td>
				</tr>
			</table>
			</fieldset>
			<p class="submit"><input type="submit" class="button-primary"
				value="<?php _e('Save Changes') ?>" /></p>
			</form>
			<p><?php echo sprintf(__( 'Why don\'t you <a href="%s">head on over and upgrade now</a>.','wp-beta-tester' ), 'update-core.php');  ?></p>
		</div>
	</div>
<?php
	}
}
/* Initialise outselves */
add_action('plugins_loaded', create_function('','global $wp_beta_tester_instance; $wp_beta_tester_instance = new wp_beta_tester();'));

?>
