<?php
define('PREVIEW_LOOKBACK_COUNT', '20');

function build_welcome_email()
{
    global $template, $template_dir, $current_page_base, $language_page_directory, $currencies, $db;

    require(DIR_FS_CATALOG_MODULES . zen_get_module_directory('require_languages.php'));

    $last_cust = $db->Execute("SELECT customers_firstname, customers_lastname FROM " . TABLE_CUSTOMERS . " ORDER BY customers_id DESC  LIMIT " . PREVIEW_LOOKBACK_COUNT);
    $last_cust = preview_advance($last_cust);

    $content['EMAIL_SUBJECT'] = EMAIL_SUBJECT;
    $content['EMAIL_CONTACT_OWNER'] = EMAIL_CONTACT;
    $content['EMAIL_WELCOME'] = EMAIL_WELCOME;
    $content['EMAIL_MESSAGE_HTML'] = EMAIL_TEXT;
    $content['EMAIL_CLOSURE'] = EMAIL_GV_CLOSURE;
    $content['EMAIL_GREETING'] = sprintf(EMAIL_GREET_NONE, $last_cust->fields['customers_firstname']);
    return $content;
}

function build_checkout_email()
{
    global $template, $template_dir, $current_page_base, $language_page_directory, $currencies, $db;

    require(DIR_FS_CATALOG_MODULES . zen_get_module_directory('require_languages.php'));

    $last_order = $db->Execute("SELECT orders_id FROM " . TABLE_ORDERS . " ORDER BY orders_id DESC  LIMIT " . PREVIEW_LOOKBACK_COUNT);
    $last_order = preview_advance($last_order);
    $oID = $last_order->fields['orders_id'];
    $content['EMAIL_SUBJECT'] = EMAIL_TEXT_SUBJECT . EMAIL_ORDER_NUMBER_SUBJECT . $oID;
    include(DIR_WS_CLASSES . 'order.php');
    $order = new order($oID);
    $parts = preg_split('/\s+/', $order->customer['name']);
    $content['EMAIL_FIRST_NAME'] = '';
    for ($i = 0; $i < (sizeof($parts) - 1); $i++) {
        $content['EMAIL_FIRST_NAME'] .= $parts[$i] . " ";
    }
    $content['EMAIL_LAST_NAME'] = $parts[$i];
    $content['INTRO_URL_TEXT'] = EMAIL_TEXT_INVOICE_URL_CLICK;
    $content['INTRO_URL_VALUE'] = zen_catalog_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID, 'SSL', false);
    $content['EMAIL_TEXT_HEADER'] = EMAIL_TEXT_HEADER;
    $content['EMAIL_TEXT_FROM'] = EMAIL_TEXT_FROM;
    $content['INTRO_STORE_NAME'] = STORE_NAME;
    $content['EMAIL_THANKS_FOR_SHOPPING'] = EMAIL_THANKS_FOR_SHOPPING;
    $content['EMAIL_DETAILS_FOLLOW'] = EMAIL_DETAILS_FOLLOW;
    $content['INTRO_ORDER_NUM_TITLE'] = EMAIL_TEXT_ORDER_NUMBER;
    $content['INTRO_ORDER_NUMBER'] = $oID;
    $content['INTRO_DATE_TITLE'] = EMAIL_TEXT_DATE_ORDERED;
    $content['INTRO_DATE_ORDERED'] = strftime(DATE_FORMAT_LONG);
    $content['PRODUCTS_TITLE'] = EMAIL_TEXT_PRODUCTS;

    // Order comments?
    $orders_history = $db->Execute("SELECT  * FROM  " .
        TABLE_ORDERS_STATUS_HISTORY . "
                                 where orders_id = '" . zen_db_input($oID) . "'
                                 ORDER BY date_added ASC LIMIT 1");
    $content['ORDER_COMMENTS'] = nl2br(zen_db_output($orders_history->fields['comments']));
    $content['HEADING_ADDRESS_INFORMATION'] = HEADING_ADDRESS_INFORMATION;
    $content['ADDRESS_DELIVERY_TITLE'] = EMAIL_TEXT_DELIVERY_ADDRESS;
    $content['ADDRESS_DELIVERY_DETAIL'] = zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />');
    $content['ADDRESS_BILLING_TITLE'] = EMAIL_TEXT_BILLING_ADDRESS;
    $content['ADDRESS_BILLING_DETAIL'] = zen_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />');
    $content['SHIPPING_METHOD_TITLE'] = HEADING_SHIPPING_METHOD;
    $content['SHIPPING_METHOD_DETAIL'] = (zen_not_null($order->info['shipping_method'])) ? $order->info['shipping_method'] : 'n/a';
    $content['PAYMENT_METHOD_TITLE'] = EMAIL_TEXT_PAYMENT_METHOD;
    $content['PAYMENT_METHOD_DETAIL'] = $order->info['payment_method'];
    $content['PAYMENT_METHOD_FOOTER'] = '';

    // OT and cart contents
    $html_ot = '<tr><td class="order-totals-text" align="right" width="100%">' . '&nbsp;' . '</td> ' . "\n" . '<td class="order-totals-num" align="right" nowrap="nowrap">' . '---------' . '</td> </tr>' . "\n";
    for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
        $email_order .= strip_tags($order->totals[$i]['title']) . ' ' . strip_tags($order->totals[$i]['text']) . "\n";
        $html_ot .= '<tr><td class="order-totals-text" align="right" width="100%">' . $order->totals[$i]['title'] . '</td> ' . "\n" . '<td class="order-totals-num" align="right" nowrap="nowrap">' . ($order->totals[$i]['text']) . '</td> </tr>' . "\n";
    }
    $content['ORDER_TOTALS'] = '<table border="0" width="100%" cellspacing="0" cellpadding="2"> ' . $html_ot . ' </table>';

    for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
        $attributes = '';
        if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
            for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
                $attributes = '<br /><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . nl2br(zen_output_string_protected($order->products[$i]['attributes'][$j]['value']));
                if ($order->products[$i]['attributes'][$j]['price'] != '0') $attributes .= ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
                if ($order->products[$i]['attributes'][$j]['product_attribute_is_free'] == '1' and $order->products[$i]['product_is_free'] == '1') $attributes .= TEXT_INFO_ATTRIBUTE_FREE;
                $attributes .= '</i></small></nobr>';
            }
        }
        $order->products_ordered_html .=
            '<tr>' . "\n" .
            '<td class="product-details" align="right" valign="top" width="30">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
            '<td class="product-details" valign="top">' . nl2br($order->products[$i]['name']) . ($order->products[$i]['model'] != '' ? ' (' . nl2br($order->products[$i]['model']) . ') ' : '') . "\n" .
            '<nobr>' .
            '<small><em> ' . nl2br($attributes) . '</em></small>' .
            '</nobr>' .
            '</td>' . "\n" .
            '<td class="product-details-num" valign="top" align="right">' .
            $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) .
            ($order->products[$i]['onetime_charges'] != 0 ?
                '</td></tr>' . "\n" . '<tr><td class="product-details">' . nl2br(TEXT_ONETIME_CHARGES_EMAIL) . '</td>' . "\n" .
                '<td>' . $currencies->display_price($order->products[$i]['onetime_charges'], $order->products[$i]['tax'], 1) : '') .
            '</td></tr>' . "\n";
    }
    $content['PRODUCTS_DETAIL'] = '<table class="product-details" border="0" width="100%" cellspacing="0" cellpadding="2">' . $order->products_ordered_html . '</table>';

    return $content;
}

function build_back_in_stock_email()
{
    global $template, $template_dir, $current_page_base, $language_page_directory, $db, $currencies;

    require(DIR_FS_CATALOG_MODULES . zen_get_module_directory('require_languages.php'));
    require($language_page_directory . "/extra_definitions/back_in_stock.php");

    $bis_product_query = $db->Execute("SELECT products_id FROM " . TABLE_PRODUCTS . " WHERE products_status = 1 ORDER BY products_last_modified DESC LIMIT " . PREVIEW_LOOKBACK_COUNT);
    $bis_product_query = preview_advance($bis_product_query);
    $product_id = $bis_product_query->fields['products_id'];
    $content['CUSTOMERS_NAME'] = "Mr. Prospect";
    $content['PRODUCT_NAME'] = str_replace("<br/>", " ", zen_get_products_name($product_id));
    $content['EMAIL_SUBJECT'] = "Order " . $content['PRODUCT_NAME'] . " now at " . STORE_NAME;
    $content['SPAM_LINK'] = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=back_in_stock&bis_id=' . $bis_id;
    $content['TOP_MESSAGE'] = BACK_IN_STOCK_MAIL_TOP . $content['PRODUCT_NAME'] . "\n" . "\n" . BACK_IN_STOCK_MAIL_AVAILABLE;
    if (BACK_IN_STOCK_DESC_IN_EMAIL == 1) {
        $content['PRODUCT_DESCRIPTION'] = zen_get_products_description($product_id);
    } else {
        $content['PRODUCT_DESCRIPTION'] = " ";
    }
    $products_image = zen_products_lookup($product_id, 'products_image');
    $content['PRODUCT_IMAGE'] = '<img style="max-width:100%;" src="' . HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . DIR_WS_IMAGES . $products_image . '" alt="' . $content['PRODUCT_NAME'] . '">';
    $content['PRODUCT_LINK'] = zen_catalog_href_link('product_info', 'products_id=' . $product_id);
    $content['BOTTOM_MESSAGE'] = BACK_IN_STOCK_MAIL_BOTTOM;
    return $content;
}

function build_abandoned_cart_email($drip_number)
{
    // logic from admin/recover_cart_sales.php 'sendmail' portion
    // new home: includes/functions/extra_functions/abandoned_cart.php,
    global $template, $template_dir, $current_page_base, $language_page_directory, $currencies, $db;

    require(DIR_FS_CATALOG_MODULES . zen_get_module_directory('require_languages.php'));

    $cid_query = $db->Execute("SELECT customers_id FROM " . TABLE_CUSTOMERS_BASKET . " ORDER BY customers_basket_date_added DESC LIMIT " . PREVIEW_LOOKBACK_COUNT);
    $cid_query = preview_advance($cid_query);
    $cid = $cid_query->fields['customers_id'];

    $basket = $db->Execute("SELECT cb.products_id,
                              cb.customers_basket_quantity,
                              cb.customers_basket_date_added,
                              cus.customers_firstname fname,
                              cus.customers_lastname lname,
                              cus.customers_email_address email
                     FROM      " . TABLE_CUSTOMERS_BASKET . " cb,
                     " . TABLE_CUSTOMERS . " cus
                     WHERE     cb.customers_id = cus.customers_id AND
                     cus.customers_id ='" . $cid . "'
                     ORDER BY  cb.customers_basket_date_added DESC ");
    if ($basket->EOF) {
        // should not happen
        return;
    }

    $html = "";
    $text = "";
    $product_list = array();
    while (!$basket->EOF) {
        $prid = (int)$basket->fields['products_id'];
        if (in_array($prid, $product_list)) {
            $basket->MoveNext();
            continue;
        }
        $product_list[] = $prid;
        $products = $db->Execute("SELECT p.products_model model, pd.products_name name
             FROM " . TABLE_PRODUCTS . " p,
             " . TABLE_PRODUCTS_DESCRIPTION . " pd
             WHERE p.products_id = '" . $basket->fields['products_id'] . "'
             AND pd.products_id = p.products_id
             AND pd.language_id = " . (int)$_SESSION['languages_id']);


        $image_file = zen_products_lookup($basket->fields['products_id'], 'products_image');
        $products_image = '<img style="max-width:100%;" src="' . HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . DIR_WS_IMAGES . $image_file . '" alt="' . $basket->fields['products_name'] . '">';
        $link = '<a href="' . zen_catalog_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $basket->fields['products_id']) . '">';
        $html .= '<tr><td width="50%">' . $link . $products_image . '</a></td><td>' . $link . $products->fields['name'] . '</a></td></tr>';

        $text .= $products->fields['name'] . ": " . zen_catalog_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $basket->fields['products_id']) . "\n\n";
        $basket->MoveNext();
    }

    $email = '';

   if ($drip_number == 1) {
      $email .= $basket->fields['fname'] . EMAIL_MESSAGE_1;
      $drip_template = 'abandoned_cart_1';
      $attention_line = ATTENTION_1; 
   } else if ($drip_number == 2) {
      $email .= $basket->fields['fname'] . EMAIL_MESSAGE_2;
      $drip_template = 'abandoned_cart_2';
      $attention_line = ATTENTION_2; 
   } else if ($drip_number == 3) {
      $email .= $basket->fields['fname'] . EMAIL_MESSAGE_3;
      $drip_template = 'abandoned_cart_3';
      $attention_line = ATTENTION_3; 
   }

    $email_text = $header_msg . $text;
    $email_html = nl2br($header_msg) .
        '<table border="0" width="100%" cellspacing="0" cellpadding="2"> ' . $html . ' </table>';

   // drip 3 doesn't show cart 
   if ($drip_number == 3) {
      $text = CONTACT_BLOCK_3; 
      $html = CONTACT_BLOCK_3; 
      $email_text = $header_msg . $text;
      $email_html = nl2br($header_msg) . $html; 
   }

    $content['EMAIL_MESSAGE_HTML'] = $email_html;
    $content['EMAIL_SUBJECT'] = EMAIL_TEXT_SUBJECT;
    $content['EMAIL_ATTENTION'] = $attention_line;

    return $content;
}

function build_abandoned_cart_base_email()
{
    // logic from admin/recover_cart_sales.php 'sendmail' portion
    // new home: includes/functions/extra_functions/abandoned_cart.php,
    global $template, $template_dir, $current_page_base, $language_page_directory, $currencies, $db;

    require(DIR_FS_CATALOG_MODULES . zen_get_module_directory('require_languages.php'));

    $cid_query = $db->Execute("SELECT customers_id FROM " . TABLE_CUSTOMERS_BASKET . " ORDER BY customers_basket_date_added DESC LIMIT " . PREVIEW_LOOKBACK_COUNT);
    $cid_query = preview_advance($cid_query);
    $cid = $cid_query->fields['customers_id'];

    $basket = $db->Execute("SELECT cb.products_id,
                              cb.customers_basket_quantity,
                              cb.customers_basket_date_added,
                              cus.customers_firstname fname,
                              cus.customers_lastname lname,
                              cus.customers_email_address email
                     FROM      " . TABLE_CUSTOMERS_BASKET . " cb,
                     " . TABLE_CUSTOMERS . " cus
                     WHERE     cb.customers_id = cus.customers_id AND
                     cus.customers_id ='" . $cid . "'
                     ORDER BY  cb.customers_basket_date_added DESC ");
    if ($basket->EOF) {
        // should not happen
        return;
    }

    $html = "";
    $text = "";
    $mline = "";
    $cline = "";
    $product_list = array();
    while (!$basket->EOF) {
        $prid = (int)$basket->fields['products_id'];
        if (in_array($prid, $product_list)) {
            $basket->MoveNext();
            continue;
        }
        $product_list[] = $prid;
        $products = $db->Execute("SELECT p.products_model model, pd.products_name name
             FROM " . TABLE_PRODUCTS . " p,
             " . TABLE_PRODUCTS_DESCRIPTION . " pd
             WHERE p.products_id = '" . $basket->fields['products_id'] . "'
             AND pd.products_id = p.products_id
             AND pd.language_id = " . (int)$_SESSION['languages_id']);


        $sprice = zen_get_products_actual_price($basket->fields['products_id']);

        $tprice += $basket->fields['customers_basket_quantity'] * $sprice;

        $cline .= "<tr class='dataTableRow'>
                                     <td class='dataTableContent' align='left' width='15%'>" . $products->fields['model'] . "</td>
                                            <td class='dataTableContent' align='left' colspan='2' width='55%'><a href='" . zen_href_link(FILENAME_CATEGORIES, 'action=new_product_preview&read=only&pID=' . $basket->fields['products_id'] . '&origin=' . FILENAME_RECOVER_CART_SALES . '?page=' . $_GET['page']) . "'>" . $products->fields['name'] . "</a></td>
                                            <td class='dataTableContent' align='center' width='10%'>" . $basket->fields['customers_basket_quantity'] . "</td>
                                            <td class='dataTableContent' align='right' width='10%'>" . $currencies->format($sprice) . "</td>
                                            <td class='dataTableContent' align='right' width='10%'>" . $currencies->format($basket->fields['customers_basket_quantity'] * $sprice) . "</td>
                                     </tr>";

        $mline .= $basket->fields['customers_basket_quantity'] . ' x ' . $products->fields['name'] . "\n";
        $mline .= '   <blockquote><a href="' . zen_catalog_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $basket->fields['products_id']) . '">' . zen_catalog_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $basket->fields['products_id']) . "</a></blockquote>\n\n";
        $basket->MoveNext();
    }
    $cline .= "</td></tr>";

    $email = '';

    if (RCS_EMAIL_FRIENDLY == 'true') {
        $email .= EMAIL_TEXT_SALUTATION . $basket->fields['fname'] . ' ' . $basket->fields['lname'] . ",";
    } else {
        $email .= STORE_NAME . "\n" . EMAIL_SEPARATOR . "\n";
    }

    $cquery = $db->Execute("SELECT * FROM " . TABLE_ORDERS . " WHERE customers_id = '" . $cid . "'");
    if ($cquery->RecordCount() < 1) {
        $email .= sprintf(EMAIL_TEXT_NEWCUST_INTRO, $mline);
    } else {
        $email .= sprintf(EMAIL_TEXT_CURCUST_INTRO, $mline);
    }

    $email .= EMAIL_TEXT_BODY_HEADER . $mline . EMAIL_TEXT_BODY_FOOTER;

    if (EMAIL_USE_HTML == 'true')
        $email .= '<a href="' . zen_catalog_href_link(FILENAME_DEFAULT) . '">' . STORE_OWNER . "\n" . zen_catalog_href_link(FILENAME_DEFAULT) . '</a>';
    else
        $email .= STORE_OWNER . "\n" . zen_catalog_href_link(FILENAME_DEFAULT);

    $email .= "\n\n";

    $email .= "\n" . EMAIL_SEPARATOR . "\n\n";
    $email .= EMAIL_TEXT_LOGIN;

    if (EMAIL_USE_HTML == 'true')
        $email .= '  <a href="' . zen_catalog_href_link(FILENAME_LOGIN, '', 'SSL') . '">' . zen_catalog_href_link(FILENAME_LOGIN, '', 'SSL') . '</a>';
    else
        $email .= '  (' . zen_catalog_href_link(FILENAME_LOGIN, '', 'SSL') . ')';

    $custname = $basket->fields['fname'] . " " . $basket->fields['lname'];
    $outEmailAddr = '"' . $custname . '" <' . $basket->fields['email'] . '>';
    $content['EMAIL_MESSAGE_HTML'] = nl2br($email) . zen_db_prepare_input($_POST['message_html']);
    $email = strip_tags($email . "\n\n" . zen_db_prepare_input($_POST['message']));

//                   '<table border="0" width="100%" cellspacing="0" cellpadding="2"> ' . $html. ' </table>';

    $content['EMAIL_SUBJECT'] = EMAIL_TEXT_SUBJECT;

    return $content;
}

function preview_advance($query)
{
    global $db;

    $iters = zen_rand(0, PREVIEW_LOOKBACK_COUNT - 1);
    while ($iters > 0) {
        $query->MoveNext();
        $iters--;
    }
    return $query;
}
