<?php
/**
 * The PHP Skeleton App
 *
 * @author      Goran Halusa <gor@webcraftr.com>
 * @copyright   2015 Goran Halusa
 * @link        https://github.com/ghalusa/PHP-Skeleton-App
 * @license     https://github.com/ghalusa/PHP-Skeleton-App/wiki/License
 * @version     1.0.0
 * @package     PHP Skeleton App
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Show Dashboard
 *
 * Controller for the Dashboard module.
 *
 * @author      Goran Halusa <gor@webcraftr.com>
 * @since       1.0.0
 */

function show_dashboard(){
	$app = \Slim\Slim::getInstance();
	$final_global_template_vars = $app->config('final_global_template_vars');
	$app->render('modules_dashboard.php',array(
		"page_title" => "Dashboard"
	));
}
?>