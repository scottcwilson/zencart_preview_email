<?php
require("includes/application_top.php"); 

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

// Pulling in catalog side functions 
if (!function_exists('zen_get_module_directory')) { 
  function zen_get_module_directory($check_file, $dir_only = 'false') {
    global $template_dir;

    $zv_filename = $check_file;
    if (!strstr($zv_filename, '.php')) $zv_filename .= '.php';

    if (file_exists(DIR_FS_CATALOG_MODULES . $template_dir . '/' . $zv_filename)) {
      $template_dir_select = $template_dir . '/';
    } else {
      $template_dir_select = '';
    }

    if ($dir_only == 'true') {
      return $template_dir_select;
    } else {
      return $template_dir_select . $zv_filename;
    }
  }
}

// basic settings for all emails 
$language_page_directory = DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/';
$common['EMAIL_COMMON_CSS'] = file_get_contents (DIR_FS_EMAIL_TEMPLATES . $css_lang_folder . 'email_common.css');

if (file_exists(DIR_FS_CATALOG_LANGUAGES. $_SESSION['language'] . '/' . $template_dir . '/' . FILENAME_EMAIL_EXTRAS)) {
  $template_dir_select = $template_dir . '/';
} else {
  $template_dir_select = '';
}
require_once(DIR_FS_CATALOG_LANGUAGES. $_SESSION['language'] . '/' . $template_dir_select . FILENAME_EMAIL_EXTRAS);

$action = $_POST['action']; 
if (!empty($action)) {
   if ($action == 'checkout') {
      $current_page_base = 'checkout_process'; 
      $module = 'checkout'; 
      $content = build_checkout_email(); 
   } else if ($action == 'contact_us') {
      $current_page_base = 'contact_us'; 
      $module = 'contact_us'; 
      $content = build_generic_email($module); 
   } else if ($action == 'coupon') {
      $current_page_base = 'coupon'; 
      $module = 'coupon'; 
      $content = build_coupon_email(); 
   } else if ($action == 'default') {
      $current_page_base = 'default'; 
      $module = 'default'; 
      $content = build_generic_email($module); 
   } else if ($action == 'direct') {
      $current_page_base = 'direct'; 
      $module = 'direct'; 
      $content = build_generic_email($module); 
   } else if ($action == 'gv_queue') {
      $current_page_base = 'gv_queue'; 
      $module = 'gv_queue'; 
      $content = build_gv_email($module); 
   } else if ($action == 'gv_mail') {
      $current_page_base = 'gv_mail'; 
      $module = 'gv_mail'; 
      $content = build_gv_email($module); 
   } else if ($action == 'gv_send') {
      $current_page_base = 'gv_send'; 
      $module = 'gv_send'; 
      $content = build_gv_email($module); 
   } else if ($action == 'order_status') {
      $current_page_base = 'order_status'; 
      $module = 'order_status'; 
      $content = build_order_status_email($module); 
   } else if ($action == 'product_notification') {
      $current_page_base = 'product_notification'; 
      $module = 'product_notification'; 
      $content = build_generic_email($module); 
   } else if ($action == 'password_forgotten') {
      $current_page_base = 'password_forgotten'; 
      $module = 'password_forgotten'; 
      $content = build_generic_email($module); 
   } else if ($action == "welcome") { 
      $current_page_base = 'create_account'; 
      $module = 'welcome';
      $content = build_welcome_email(); 
   } else if ($action == 'back_in_stock') {
      $current_page_base = 'back_in_stock_notification'; 
      $module = 'back_in_stock_notification'; 
      $content = build_back_in_stock_email(); 
   } else if ($action == 'abandoned_cart_base') {
      $current_page_base = 'recover_cart_sales'; 
      $module = 'recover_cart_sales'; 
      $content = build_abandoned_cart_base_email(); 
   } else if ($action == 'abandoned_cart_1') {
      $current_page_base = 'recover_cart_sales'; 
      $module = 'abandoned_cart_1'; 
      $content = build_abandoned_cart_email(1); 
   } else if ($action == 'abandoned_cart_2') {
      $current_page_base = 'recover_cart_sales'; 
      $module = 'abandoned_cart_2'; 
      $content = build_abandoned_cart_email(2); 
   } else if ($action == 'abandoned_cart_3') {
      $current_page_base = 'recover_cart_sales'; 
      $module = 'abandoned_cart_3'; 
      $content = build_abandoned_cart_email(3); 
   }

   if (function_exists('preview_email_custom')) {
      preview_email_custom($action, $content); 
   }   
 
   if (!empty($action)) { 
      foreach ($content as $key=>$value) {
       if ($key == 'ORDER_TOTALS') continue;
       if ($key == 'PRODUCTS_DETAIL') continue;
        $content[$key] = nl2br($value); 
      }
      $file_holder = zen_build_html_email_from_template($module, $content); 
      echo $file_holder;
   }
} else {
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<link rel="stylesheet" type="text/css" href="includes/admin_access.css" />
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
</script>
</head>
<body onload="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<div id="pageWrapper">
  <h1><?php echo HEADING_TITLE ?></h1>
<?php 
  echo WHICH_EMAIL . "<br />"; 

echo zen_draw_form('preview_email', "preview_email.php", '', 'post', 'target="_blank"'); 

echo zen_hide_session_id(); 
?>
<br /><br />
<div style="margin-left: 10px;">
<?php 

echo zen_draw_radio_field('action', 'checkout', false) . " " . CHECKOUT_EMAIL_NAME; 
echo "<br /><br />"; 
echo zen_draw_radio_field('action', 'contact_us', false) . " " . CONTACT_US_EMAIL_NAME; 
echo "<br /><br />"; 
echo zen_draw_radio_field('action', 'coupon', false) . " " . COUPON_EMAIL_NAME; 
echo "<br /><br />"; 
echo zen_draw_radio_field('action', 'default', false) . " " . DEFAULT_EMAIL_NAME; 
echo "<br /><br />"; 
echo zen_draw_radio_field('action', 'direct', false) . " " . DIRECT_EMAIL_NAME; 
echo "<br /><br />"; 
if (defined('MODULE_ORDER_TOTAL_GV_STATUS') && MODULE_ORDER_TOTAL_GV_STATUS == 'true') {
   echo zen_draw_radio_field('action', 'gv_mail', false) . " " . GV_MAIL_EMAIL_NAME; 
   echo "<br /><br />"; 
   echo zen_draw_radio_field('action', 'gv_queue', false) . " " . GV_QUEUE_EMAIL_NAME; 
   echo "<br /><br />"; 
   echo zen_draw_radio_field('action', 'gv_send', false) . " " . GV_SEND_EMAIL_NAME; 
   echo "<br /><br />"; 
}
echo zen_draw_radio_field('action', 'order_status', false) . " " . ORDER_STATUS_EMAIL_NAME; 
echo "<br /><br />"; 
echo zen_draw_radio_field('action', 'password_forgotten', false) . " " . PASSWORD_FORGOTTEN_EMAIL_NAME; 
echo "<br /><br />"; 
echo zen_draw_radio_field('action', 'product_notification', false) . " " . PRODUCT_NOTIFICATION_EMAIL_NAME ; 
echo "<br /><br />"; 
echo zen_draw_radio_field('action', 'welcome', false) . " " . WELCOME_EMAIL_NAME; 
echo "<br /><br />"; 

echo "<hr />"; 
// Optional - based on add-ons 
if (file_exists(DIR_FS_EMAIL_TEMPLATES . 'email_template_back_in_stock_notification.html')) {
   echo zen_draw_radio_field('action', 'back_in_stock', false) . " " . BACK_IN_STOCK_EMAIL_NAME; 
   echo "<br />"; 
}
if (file_exists(DIR_FS_EMAIL_TEMPLATES . 'email_template_abandoned_cart_1.html')) {
   // customized abandon cart 
   echo zen_draw_radio_field('action', 'abandoned_cart_1', false) . " " . ABANDONED_CART_EMAIL_NAME . " 1"; 
   echo "<br />"; 
} else { 
   // see if they have done the updates for Recover Cart Sales
   if (file_exists(DIR_FS_CATALOG_LANGUAGES. $_SESSION['language'] . '/' . 'recover_cart_sales.php')) { 
      echo zen_draw_radio_field('action', 'abandoned_cart_base', false) . " " . ABANDONED_CART_EMAIL_NAME; 
      echo "<br />"; 
   }
}
if (file_exists(DIR_FS_EMAIL_TEMPLATES . 'email_template_abandoned_cart_2.html')) {
   echo zen_draw_radio_field('action', 'abandoned_cart_2', false) . " " . ABANDONED_CART_EMAIL_NAME . " 2"; 
   echo "<br />"; 
}
if (file_exists(DIR_FS_EMAIL_TEMPLATES . 'email_template_abandoned_cart_3.html')) {
   echo zen_draw_radio_field('action', 'abandoned_cart_3', false) . " " . ABANDONED_CART_EMAIL_NAME . " 3"; 
   echo "<br />"; 
}
?>
<br />
<br />
                <?php echo zen_image_submit('button_preview.gif', BOX_CUSTOMERS_PREVIEW_EMAIL); ?>
<br />
</div>
</form>
</div>
<!-- body_eof //-->

<div class="bottom">
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</div>
<br>
</body>
</html>
<?php 
}
