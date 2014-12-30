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
 * Controller for the Group module.
 *
 * @author Goran Halusa <gor@webcraftr.com>
 * @copyright   2014 Goran Halusa
 * @link        https://github.com/ghalusa/PHP-Skeleton-App
 * @license     https://github.com/ghalusa/PHP-Skeleton-App/wiki/License
 * @version     1.0.0
 * @package     PHP Skeleton App
 */

function datatables_browse_groups(){
	$app = \Slim\Slim::getInstance();
	global $final_global_template_vars;
	require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/group.class.php";
	$db_conn = new \slimlocal\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$group = new Group($db_resource,$final_global_template_vars["session_key"]);

	$search = $app->request()->post('search');
  $search_value = !empty($search["value"]) ? $search["value"] : false;

	$data = $group->browse_groups(
		false,
		$app->request()->post('order'),
		$app->request()->post('start'),
		$app->request()->post('length'),
		$search_value
	);

	echo json_encode($data);
	die();
}
?>