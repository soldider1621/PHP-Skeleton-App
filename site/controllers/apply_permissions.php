<?php
/**
 * This file is part of PHP Skeleton App.
 *
 * (c) 2014 Goran Halusa
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Controller for the Site module.
 *
 * @author Goran Halusa <gor@webcraftr.com>
 * @copyright   2014 Goran Halusa
 * @link        https://github.com/ghalusa/PHP-Skeleton-App
 * @license     https://github.com/ghalusa/PHP-Skeleton-App/wiki/License
 * @version     1.0.0
 * @package     PHP Skeleton App
 */

/**
 * This function is used ONLY to make sure if a user has a sufficient role to be on a page...
 * NOT to apply permissions as to what the user can view ON that page.
 */

global $apply_permissions;
$apply_permissions = function($role_perm_key = array()) {
	return function ($redirect = true) use ($role_perm_key) {
		global $final_global_template_vars;
		$user_roles = !empty($_SESSION[$final_global_template_vars["session_key"]]) && !empty($_SESSION[$final_global_template_vars["session_key"]]["user_role_list"]) ? $_SESSION[$final_global_template_vars["session_key"]]["user_role_list"] : array();
		$has_permission = array_intersect($user_roles, $final_global_template_vars[$role_perm_key]);
		if(empty($redirect)) {
			if(empty($has_permission)) {
				return false;
			} else {
				return true;
			}
		} else {
			if(empty($has_permission)) {
				$app = \Slim\Slim::getInstance();
				$app->redirect($final_global_template_vars["access_denied_url"]);
			}
		}
	};
};
?>