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
 * Browse Groups
 *
 * Controller for the Group module.
 *
 * @author      Goran Halusa <gor@webcraftr.com>
 * @since       1.0.0
 */

function browse_groups(){
	$app = \Slim\Slim::getInstance();
	$app->render('browse_groups.php',array(
		"page_title" => "Browse Groups"
	));
}
?>