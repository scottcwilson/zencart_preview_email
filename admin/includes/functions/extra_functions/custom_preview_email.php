<?php
function preview_email_custom($action, &$content) {
   global $order, $oID, $db; 
   if ($action == "checkout") {
      if (defined('EMAIL_TEXT_DELIVERY_DATE')) { 
         $delivery_date_query = $db->Execute("SELECT order_delivery_date FROM " . TABLE_ORDERS . " WHERE orders_id = " . (int)$oID); 
         $delivery_date = $delivery_date_query->fields['order_delivery_date']; 
         if (empty($delivery_date)) { 
            $content['EMAIL_TEXT_DELIVERY_DATE'] = "Desired delivery date not specified.";
         } else { 
            $content['EMAIL_TEXT_DELIVERY_DATE'] = EMAIL_TEXT_DELIVERY_DATE . $order_delivery_date; 
         }
      }
   }
}
