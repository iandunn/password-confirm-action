<?php
/**
 * Plugin Name: Password Confirm Action
 * Plugin URI:  http://github.com/stephenharris/password-confirm-action
 * Description: Prompts the user for their password whenever they try to perform an action which could be used by an attacker to escalate privileges or engineer future access.
 * Version:     0.2.0
 * Author:      Stephen Harris
 * Author URI:  stephenharris.info
 * License:     GPLv2+
 * Text Domain: password-confirm-action
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2015 Stephen Harris
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Inspiration for this plug-in came from Human Made's Require Password plugin https://github.com/humanmade/hm-require-password
 * by Jenny Wong.
 */

class PasswordConfirmAction {

	/**
	 * Wires up the plug-in's hooks.
	 */
	static function hook(){

		//Add fields for editing user's own profile
		add_action( 'show_user_profile', array( __CLASS__, 'print_password_fields' ) );

		//Add fields for editing another user's profile
		add_action( 'edit_user_profile', array( __CLASS__, 'print_password_fields' ) );

		//Add fields for creating a new user
		add_action( 'user_new_form', array( __CLASS__, 'print_password_fields' ) );

		//Validating editing / creating a user - check if current user's password is required and if so, correct.
		add_action( 'user_profile_update_errors', array( __CLASS__, 'validate_user_update' ), 1, 3 );

		//Register plugins scripts/styles
		add_action( 'init', array( __CLASS__, 'register_scripts' ) );

	}

	static function register_scripts(){
		wp_register_script( 'password-confirm-action', plugin_dir_url( __FILE__ ) . 'password-confirm-action.js' );
		wp_register_style( 'password-confirm-action', plugin_dir_url( __FILE__ ) . 'password-confirm-action.css' );
	}

	static function print_password_fields( $user = null ){

		wp_enqueue_script( 'password-confirm-action' );
		wp_enqueue_style( 'password-confirm-action' );

		wp_localize_script( 'password-confirm-action', 'pca', array(
			'user' => array(
				'email' => ( $user instanceof WP_User ) ? $user->user_email : false,
				'roles' => ( $user instanceof WP_User ) ? $user->roles : array(),
			)
		) );

		switch ( current_filter() ){ //Set labels / description according to context

			case 'user_new_form': //Creating a new user
				$label_js = $label_no_js = __( 'Confirm your current password', 'password-confirm-action' );
				$description_js = $description_no_js = esc_html__( 'Please cofirm your password to create a new user.', 'password-confirm-action' );
				break;

			case 'show_user_profile': //Editing user's own profile
			case 'edit_user_profile': //Editing another user's profile
			default:
				$label_js = $label_no_js = __( 'Confirm your current password', 'password-confirm-action' );
				$description_no_js = esc_html__( 'If you would like to set a new password, role or e-mail for this user, please cofirm your password here.', 'password-confirm-action' );
				$description_js = esc_html__( 'Please cofirm your password to update this user.', 'password-confirm-action' );
				break;

		}

		//Display form fields: no-js fallback and modal field.
		?>
		<table id="pca-fields" class="form-table hide-if-js">
			<tbody>
				<tr id="current-password" class="user-description-wrap">
					<th scope="row"><label for="current-pass"><?php echo esc_html( $label_no_js ); ?></label></th>
					<td>
						<input type="password" name="current_user_pass" id="current_pass" class="regular-text" size="16" value="" autocomplete="off" />
						<p class="description">
							<?php echo $description_no_js; ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		
		<div id="pca-auth-check-wrap" class="hidden hide-if-no-js" aria-hidden="true" tabIndex="-1" role="dialog">
			<div id="pca-auth-check-bg"></div>
			<div id="pca-auth-check">
				<div class="pca-auth-check-close" tabindex="0" title="<?php esc_attr_e( 'Close', 'password-confirm-action' ); ?>"></div>
					<p>
						<label for="current-pass-modal"><?php echo esc_html( $label_js ); ?></label>
						<input type="password" name="" id="current-pass-modal" class="regular-text" size="16" value="" autocomplete="off" />
					</p>
					<p class="description">
						<?php echo $description_no_js; ?>
					</p>
					<?php submit_button( __( 'Confirm', 'password-confirm-action' ), 'primary' ); ?>
			</div>
		</div>
		
		<?php
	}

	/**
	 * Conditionally renders a form asking for password verification
	 * @uses PasswordConfirmAction::should_prompt_for_password()
	 */
	static function validate_user_update( $errors, $update, $user ){

		if ( self::should_prompt_for_password( $_POST, $user ) ){

			$current_user = wp_get_current_user();

			if ( empty( $_POST['current_user_pass'] ) && $user ){
				$errors->add( 'confirm-password', __( 'You must confirm your password to update this user.', 'password-confirm-action' ) );

			}elseif ( empty( $_POST['current_user_pass'] ) && ! $user ){
				$errors->add( 'confirm-password', __( 'You must confirm your password to create a user.', 'password-confirm-action' ) );

			}elseif ( ! wp_check_password( $_POST['current_user_pass'], $current_user->user_pass ) ){
				$errors->add( 'confirm-password', __( 'The current password you provided was not correct.', 'password-confirm-action' ) );
			}
		}
	}

	/**
	 * Determines whether a user should be prompted for the password
	 * $data is an array of submitted data (by the user). This function insepcts that
	 * submitted data to see if any changes would warrant a password challnege (i.e.
	 * anything that could escelate the privileges of a user, create a user or change
	 * a user's password. This includes changing an e-mail as that may be used to
	 * chang a user's password via the forgot password feature.
	 *
	 * @param array        $data        Array of data submitted by the user. Usually a copy of $_POST.
	 * @param WP_User|null $edited_user The WP_User object of the user being edited. Null if the user is being created.
	 * @return bool                     True if the user should be prompted for a password. False otherwise.
	 */
	static function should_prompt_for_password( $data, $edited_user = null ){

		$prompt = false;

		//Prompt if user's email has been changed
		if ( ! isset( $data['email'] ) || ! $edited_user || $data['email'] !== $edited_user->user_email ){
			$prompt = true;
		}

		//Prompt if user's password has been changed
		if ( ! empty( $data['pass1'] ) ){
			$prompt = true;
		}

		//Prompt if user's role has been changed
		if ( ! isset( $data['role'] ) || $data['role'] != $edited_user->role ){
			$prompt = true;
		}

		return $prompt;

	}

}

PasswordConfirmAction::hook();