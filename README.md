Zen Cart Preview Email
======================

Zen Cart provides HTML formatted emails for a variety of situations - but you have to create your own CSS. This can be time consuming to develop and test. Preview Email allows you to see how emails Zen Cart sends will look directly from your admin panel. It fabricates the fields required by the email using real data from your cart. For example, previewing the Checkout email uses order data from the last 20 checkouts in your cart. 

To run Preview Email, follow the installation instructions below, then go to Admin->Customers->Preview Email. Select one of the emails shown and press the preview button. This is what an order confirmation email looks like 
before any changes have been made. 

#  Order Confirmation Email - Original

![old format](https://www.thatsoftwareguy.com/img/site-graphics/zencart_email_preview_1.png)

Now remember that browsers cache CSS, so as you change your CSS, you'll need to clear your cache. There are a few ways of doing this, but the one I use most often is detailed in this StackOverflow post. 
The first thing we'd like to do is get rid of the pink and green. Again, there are many ways to discover the class you'd like to modify; the one I use is Chrome Devtools. This shows us that the green class is  order-detail-area. Edit email/email_common.css and change the background-color on  order-detail-area (to, say, #FFF). Similarly, change the background color on the comments class, and things will be looking better already. Refresh the admin page where you are running Preview Email, and you'll see something like this: 

# Order Confirmation Email - Phase 2

![old format](https://www.thatsoftwareguy.com/img/site-graphics/zencart_email_preview_2.png)

Much better! But now we can really notice that the box with the order line items and the box with the address information are overflowing their bounds. Using your DOM inspector, you can discover that the relevant class here is order-detail-area. The comments class is a bit too wide also. Tighten those up to 520px, and things look better. 

# Order Confirmation Email - Phase 3

![old format](https://www.thatsoftwareguy.com/img/site-graphics/zencart_email_preview_3.png)

Additional screenshots are available on my [my website](https://www.thatsoftwareguy.com/zencart_preview_email.html).

# Installation Instructions:
- Back up everything! Try this in a test environment prior to installing it on a live shop.
- Copy the contents of the unzipped folder to the root directory of your shop.
- Run the preview_email.sql file against your database, using Admin->Tools->Install SQL Patches.

The following native Zen Cart emails are supported:
- Checkout
- Contact Us
- Coupon
- Default
- Direct
- GV Mail
- GV Queue
- GV Send
- Order Status
- Password Forgotten
- Product Notification
- Welcome

Also some emails based on mods:
- Abandoned Cart (Note: [additional changes required](https://www.thatsoftwareguy.com/zencart_preview_email.html#abandoned_cart_changes))
- Back in Stock 
