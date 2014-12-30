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
 * Class for the User Account module.
 *
 * @author Goran Halusa <gor@webcraftr.com>
 * @copyright   2014 Goran Halusa
 * @link        https://github.com/ghalusa/PHP-Skeleton-App
 * @license     https://github.com/ghalusa/PHP-Skeleton-App/wiki/License
 * @version     1.0.0
 * @package     PHP Skeleton App
 */

class UserAccount {

  private $session_key = "";
  public $db;

  public function __construct( $db_connection = false, $session_key = false ) {
    if($db_connection && is_object($db_connection)) {
      $this->db = $db_connection;
    }
    $this->session_key = $session_key;
  }

  public function browse_user_accounts(
    $sort_field = false
    ,$sort_order = 'DESC'
    ,$start_record = 0
    ,$stop_record = 20
    ,$search = false
    ,$user_account_id = false) {

    $sort = "";
    $search_sql = "";
    $pdo_params = array();

    $limit_sql = " LIMIT {$start_record}, {$stop_record} ";

    if($sort_field) {
      switch($sort_field) {
        case 'last_modified':
          $sort = " ORDER BY user_account_groups.last_modified {$sort_order} ";
          break;
        default:
          $sort = " ORDER BY {$sort_field} {$sort_order} ";
        }
    }

    $and_user_account_id = $user_account_id ? " AND user_account.user_account_id = {$user_account_id} " : "";

    if($search) {
      $pdo_params[] = '%'.$search.'%';
      $pdo_params[] = '%'.$search.'%';
      $search_sql = "
        AND (
          user_account.last_name LIKE ?
          OR user_account.first_name LIKE ?
        ) ";
    }

    $statement = $this->db->prepare("
      SELECT SQL_CALC_FOUND_ROWS
        user_account_groups.user_account_id AS manage
        ,user_account_groups.user_account_id
        ,CONCAT(user_account.first_name, ' ', user_account.last_name) AS name
        ,GROUP_CONCAT(DISTINCT group.name SEPARATOR ', ') AS groups
        ,user_account_groups.user_account_id AS DT_RowId
      FROM user_account_groups
      LEFT JOIN user_account ON user_account.user_account_id = user_account_groups.user_account_id
      LEFT JOIN `group` ON `group`.group_id = user_account_groups.group_id
      WHERE 1 = 1
      {$and_user_account_id}
      {$search_sql}
      GROUP BY user_account_groups.user_account_id
      HAVING 1 = 1
      {$sort}
      {$limit_sql}");
    $statement->execute( $pdo_params );
    $data["aaData"] = $statement->fetchAll(PDO::FETCH_ASSOC);

    $statement = $this->db->prepare("SELECT FOUND_ROWS()");
    $statement->execute();
    $count = $statement->fetch(PDO::FETCH_ASSOC);
    $data["iTotalRecords"] = $count["FOUND_ROWS()"];
    $data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];
    return $data;
  }

  public function get_universal_administrator_emails() {
    $statement = $this->db->prepare("
      SELECT user_account.user_account_email
      FROM user_account
      LEFT JOIN user_account_groups ON user_account_groups.user_account_id = user_account.user_account_id
      WHERE role_id = 6
      ");
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  public function get_user_account_groups($user_account_id) {
    $statement = $this->db->prepare("
      SELECT `group`.group_id
           ,`group`.name AS group_name
      FROM user_account_groups
      LEFT JOIN `group` ON `group`.group_id = user_account_groups.group_id
      WHERE user_account_groups.user_account_id = :user_account_id
      GROUP BY `group`.group_id");
    $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  public function get_user_group_roles( $user_account_id, $group_id ) {
    $statement = $this->db->prepare("
      SELECT user_account_roles.role_id
           ,user_account_roles.label AS role_label
      FROM user_account_groups
      LEFT JOIN user_account_roles ON user_account_roles.role_id = user_account_groups.role_id
      WHERE user_account_groups.user_account_id = :user_account_id
      AND user_account_groups.group_id = :group_id");
    $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
    $statement->bindValue(":group_id", $group_id, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  public function get_roles( $exclude_ids = array() ) {
    $exclude_id_sql = "";

    if(!empty($exclude_ids)) {
      $exclude_id_sql = " AND user_account_roles.role_id NOT IN (" . implode(",",$exclude_ids) . ") ";
    }

    $statement = $this->db->prepare("
      SELECT *
      FROM user_account_roles
      WHERE 1=1
      {$exclude_id_sql}");
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  public function get_user_account_info( $user_account_id = false ) {
    $statement = $this->db->prepare("
      SELECT user_account_email
          ,first_name
          ,last_name
          ,user_account_id
      FROM user_account
      WHERE user_account_id = :user_account_id");
    $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetch(PDO::FETCH_ASSOC);
  }

  public function get_addresses( $user_account_id = false ) {
    $statement = $this->db->prepare("
      SELECT *
      FROM user_account_addresses
      WHERE user_account_id = :user_account_id");
    $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  public function insert_addresses( $data, $user_account_id, $editor_user_account_id ) {

    $address_data = array();
    $address_fields = array(
      "label"
      ,"address_1"
      ,"address_2"
      ,"city"
      ,"state"
      ,"zip"
    );

    if(isset($data["address_count"])) {

      // First, delete all user's addresses.
      $statement = $this->db->prepare("
        DELETE FROM user_account_addresses
        WHERE user_account_id = :user_account_id");
      $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
      $statement->execute();

      for($i=1; $i <= $data["address_count"]; $i++) {
        foreach($address_fields as $field) {
          $address_data[$field] = $data[$field][$i];
        }

        $statement = $this->db->prepare("
          INSERT INTO user_account_addresses
            (user_account_id
            ,address_label
            ,address_1
            ,address_2
            ,city
            ,state
            ,zip
            ,date_created
            ,created_by_user_account_id
            ,last_modified_user_account_id)
          VALUES
            (:user_account_id
            ,:address_label
            ,:address_1
            ,:address_2
            ,:city
            ,:state
            ,:zip
            ,NOW()
            ,:editor_user_account_id
            ,:editor_user_account_id)");
        $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
        $statement->bindValue(":address_label", $address_data["label"], PDO::PARAM_STR);
        $statement->bindValue(":address_1", $address_data["address_1"], PDO::PARAM_STR);
        $statement->bindValue(":address_2", $address_data["address_2"], PDO::PARAM_STR);
        $statement->bindValue(":city", $address_data["city"], PDO::PARAM_STR);
        $statement->bindValue(":state", $address_data["state"], PDO::PARAM_STR);
        $statement->bindValue(":zip", $address_data["zip"], PDO::PARAM_STR);
        $statement->bindValue(":editor_user_account_id", $editor_user_account_id, PDO::PARAM_INT);
        $statement->execute();
      }
    }

  }

  public function insert_update_user_account($data
    ,$user_account_id
    ,$update_groups = true
    ,$proxy_role_id = false
    ,$role_perm_manage_all_accounts_access = false) {

    $account_exists = $this->account_exists($user_account_id);
    if(!$account_exists) {
      // Insert
      $statement = $this->db->prepare("
        INSERT INTO user_account
        (user_account_id
        ,user_account_email
        ,user_account_password
        ,first_name
        ,last_name
        ,created_date
        ,modified_date)
        VALUES
        (:user_account_id
        ,:user_account_email
        ,:user_account_password
        ,:first_name
        ,:last_name
        ,NOW()
        ,NOW())");
      $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
      $statement->bindValue(":user_account_email", $data["user_account_email"], PDO::PARAM_STR);
      $statement->bindValue(":user_account_password", $data["user_account_password"], PDO::PARAM_STR);
      $statement->bindValue(":first_name", $data["first_name"], PDO::PARAM_STR);
      $statement->bindValue(":last_name", $data["last_name"], PDO::PARAM_STR);
      $statement->execute();
    } else {
      // Update
      $statement = $this->db->prepare("
        UPDATE user_account
        SET user_account_email = :user_account_email
        ,first_name = :first_name
        ,last_name = :last_name
        ,modified_date = NOW()
        WHERE user_account_id = :user_account_id");
      $statement->bindValue(":user_account_email", $data["user_account_email"], PDO::PARAM_STR);
      $statement->bindValue(":first_name", $data["first_name"], PDO::PARAM_STR);
      $statement->bindValue(":last_name", $data["last_name"], PDO::PARAM_STR);
      $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
      $statement->execute();
      // Update the password if user has entered one.
      if(!empty($data["user_account_password"])) {
        $statement = $this->db->prepare("
          UPDATE user_account
          SET user_account_password = :user_account_password
          ,modified_date = NOW()
          WHERE user_account_id = :user_account_id");
        $statement->bindValue(":user_account_password", sha1($data["user_account_password"]), PDO::PARAM_STR);
        $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
        $statement->execute();
      }
    }

    if( $update_groups && $role_perm_manage_all_accounts_access ) {
      // Remove all groups/roles because we are going to add them all back in.
      $this->delete_user_groups($user_account_id);
      if(isset($data["group_data"]) && $data["group_data"]) {
        $group_array = array_filter(json_decode($data["group_data"],true));
        foreach($group_array as $single_group_data){
          if(!empty($single_group_data) && !empty($single_group_data["roles"])) {
            foreach($single_group_data["roles"] as $single_role) {
              $statement = $this->db->prepare("
                INSERT INTO user_account_groups
                (role_id
                ,user_account_id
                ,group_id)
                VALUES
                (:role_id
                ,:user_account_id
                ,:group_id)");
              $statement->bindValue(":role_id", $single_role, PDO::PARAM_INT);
              $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
              $statement->bindValue(":group_id", $single_group_data["group_id"], PDO::PARAM_INT);
              $statement->execute();

              if($single_role == $proxy_role_id) {
                if(!empty($single_group_data["proxy_users"])) {
                  $user_account_groups_id = $this->db->lastInsertId();
                  foreach($single_group_data["proxy_users"] as $single_proxy_user) {
                    $statement = $this->db->prepare("
                      INSERT INTO user_account_proxy
                      (user_account_groups_id
                      ,proxy_user_account_id)
                      VALUES
                      (:user_account_groups_id
                      ,:proxy_user_account_id)");
                    $statement->bindValue(":user_account_groups_id", $user_account_groups_id, PDO::PARAM_INT);
                    $statement->bindValue(":proxy_user_account_id", $single_proxy_user["user_account_id"], PDO::PARAM_INT);
                    $statement->execute();
                  }
                }
              }
            }
          }
        }
      }
    }
  }

  public function find_user_account( $search ) {
    $statement = $this->db->prepare("
      SELECT CONCAT(first_name, ' ', last_name) AS displayname
          ,first_name
          ,last_name
          ,user_account_id
      FROM user_account
      WHERE first_name LIKE :search
      OR last_name LIKE :search
      LIMIT 20");
    $statement->bindValue(":search", "%".$search ."%", PDO::PARAM_STR);
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  public function delete_user_account( $user_account_id ) {
    $statement = $this->db->prepare("
      DELETE FROM user_account
      WHERE user_account_id = :user_account_id");
    $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
    $statement->execute();

    $statement = $this->db->prepare("
      DELETE FROM user_account_groups
      WHERE user_account_id = :user_account_id");
    $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
    $statement->execute();
  }

  public function delete_user_groups( $user_account_id ) {
    $statement = $this->db->prepare("
      DELETE FROM user_account_groups
      WHERE user_account_id = :user_account_id");
    $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
    $statement->execute();
  }

  public function account_exists( $user_account_id ) {
    $statement = $this->db->prepare("
      SELECT *
      FROM user_account
      WHERE user_account_id = :user_account_id");
    $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetch(PDO::FETCH_ASSOC);
  }

  public function account_email_exists( $user_account_email ) {
    $statement = $this->db->prepare("
      SELECT user_account_id, user_account_email
      FROM user_account
      WHERE user_account_email = :user_account_email");
    $statement->bindValue(":user_account_email", $user_account_email, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetch(PDO::FETCH_ASSOC);
  }

  public function is_registered( $user_account_id ) {
    $statement = $this->db->prepare("
      SELECT user_account.acceptable_use_policy
          ,GROUP_CONCAT(user_account_groups.group_id SEPARATOR ', ') AS groups
      FROM user_account
      LEFT JOIN user_account_groups ON user_account.user_account_id = user_account_groups.user_account_id
      WHERE user_account.user_account_id = :user_account_id
      AND user_account.acceptable_use_policy = 1
      GROUP BY user_account.user_account_id
      HAVING groups != ''");
    $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetch(PDO::FETCH_ASSOC);
  }

  // Gets all the roles that the user is associated to.
  // Returns an array all all the roles.
  public function get_user_roles_list( $user_account_id ) {
    $statement = $this->db->prepare("
      SELECT DISTINCT role_id
      FROM user_account_groups
      WHERE user_account_id = :user_account_id");
    $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  public function update_acceptable_use_policy( $user_account_id, $value ) {
    $statement = $this->db->prepare("
      UPDATE user_account
      SET acceptable_use_policy = :acceptable_use_policy
      WHERE user_account_id = :user_account_id");
    $statement->bindValue(":acceptable_use_policy", $value, PDO::PARAM_INT);
    $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
    $statement->execute();
  }

  /*
   * Gets all the proxies that the user is associated with for a specific group.
   */
  public function get_users_proxies_for_group( $user_account_id, $group_id ) {
    $statement = $this->db->prepare("
      SELECT CONCAT(user_account.first_name, ' ', user_account.last_name) AS displayname
        ,user_account.user_account_id
      FROM user_account_groups
      RIGHT JOIN user_account_proxy ON user_account_proxy.user_account_groups_id = user_account_groups.user_account_groups_id
      LEFT JOIN user_account ON user_account.user_account_id = user_account_proxy.proxy_user_account_id
      WHERE user_account_groups.user_account_id = :user_account_id
      AND user_account_groups.group_id = :group_id");
    $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
    $statement->bindValue(":group_id", $group_id, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  public function get_user_group_roles_map( $user_account_id, $proxy_id = false ) {
    $current_group_values = $this->get_user_account_groups($user_account_id);
    foreach($current_group_values as $index => $single_group) {
      $roles_array = array();
      $selected_roles = $this->get_user_group_roles($user_account_id,$single_group["group_id"]);
      $proxy_users = array();
      foreach($selected_roles as $single_role) {
        $roles_array[] = $single_role["role_id"];
        if(!empty($proxy_id) && $single_role["role_id"] == $proxy_id) {
          $proxy_users = $this->get_users_proxies_for_group($user_account_id,$single_group["group_id"]);
        }
      }
      $current_group_values[$index]["roles"] = $roles_array;
      $current_group_values[$index]["proxy_users"] = $proxy_users;
    }
    return $current_group_values;
  }

  // If you are assigned a role for a group, that role applies to all of that group's decendants.
  public function has_role( $user_account_id, $roles = array(), $group_id = false ) {
    $statement = $this->db->prepare("
      SELECT ancestor
      FROM group_closure_table
      LEFT JOIN user_account_groups ON user_account_groups.group_id = group_closure_table.ancestor
      WHERE descendant = :group_id
      AND user_account_groups.role_id IN (" . implode(",",$roles) . ")
      AND user_account_groups.user_account_id = :user_account_id");
    $statement->bindValue(":group_id", $group_id, PDO::PARAM_INT);
    $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  public function update_emailed_hash( $user_account_id = false, $emailed_hash = false ) {

    $updated = false;

    if( $user_account_id && $emailed_hash ) {

      // UPDATE the emailed_hash in the user_account table.
      $statement = $this->db->prepare("UPDATE user_account
        SET emailed_hash = :emailed_hash, active = 0
        WHERE user_account_id = :user_account_id");
      $statement->bindValue(":emailed_hash", $emailed_hash, PDO::PARAM_STR);
      $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
      $statement->execute();
      $error = $this->db->errorInfo();
      if( $error[0] != "00000" )
      {
        var_dump( $this->db->errorInfo() ); die('The UPDATE user_account emailed_hash query failed.');
      }
      $updated = true;
    }

    return $updated;
  }

  public function update_password( $user_account_password = false, $user_account_id = false, $emailed_hash = false ) {

    $updated = false;

    if( $user_account_id && $emailed_hash ) {

      // UPDATE the emailed_hash in the user_account table.
      $statement = $this->db->prepare("UPDATE user_account
        SET user_account_password = :user_account_password, active = 1
        WHERE user_account_id = :user_account_id
        AND emailed_hash = :emailed_hash");
      $statement->bindValue(":user_account_password", sha1($user_account_password), PDO::PARAM_STR);
      $statement->bindValue(":user_account_id", $user_account_id, PDO::PARAM_INT);
      $statement->bindValue(":emailed_hash", $emailed_hash, PDO::PARAM_STR);
      $statement->execute();
      $error = $this->db->errorInfo();
      if( $error[0] != "00000" )
      {
        var_dump( $this->db->errorInfo() ); die('The UPDATE user_account password query failed.');
      }
      $updated = true;
    }

    return $updated;
  }

}
?>