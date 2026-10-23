# NolaSharon Clothing website: how to manage your store

This guide is for the NolaSharon Clothing team. It explains the everyday jobs you can do yourselves in the website dashboard, without a developer.

> **Before launch:** some details are still waiting on you (see "Still to send us" at the end). Anything not yet confirmed is hidden from customers.

---

## 1. Logging in and passwords

- Go to **yourdomain.com/wp-admin** and log in with the username and password we give you at handover.
- **Change your password straight away:** top-right menu → **Edit Profile** → **New Password** → **Update Profile**. Use a long password and don't share it.
- **Staff accounts:** give each person their own login (**Users → Add New**). Choose the **Shop Manager** role for staff who handle products and orders; keep **Administrator** for the owner only. Delete accounts for people who leave.

## 2. Products

**Add a product:** **Products → Add New**.

1. **Name** at the top, then the **description** in the big box (fabric, fit, styling, care).
2. **Product short description** (lower down): one or two lines shown next to the price.
3. **Product data** box:
   - Choose **Variable product** for anything sold in sizes or colours.
   - **General tab:** fill in **Fabric / material**, **Fit** and **Care instructions**. These appear under "Details" on the product page; leave a box empty to hide it.
   - **Attributes tab:** add **Colour** and **Size**, pick the values, and tick **Used for variations**. Click **Save attributes**.
   - **Variations tab:** choose **Generate variations**. For each one, set the **Regular price**, optional **Sale price**, tick **Manage stock?** and enter the **Stock quantity**.
4. **Product categories** (right-hand side): tick Women, Men, etc.
5. **Product image** and **Product gallery** (right-hand side): the first four images make the product page gallery. Use portrait photos (4:5 works best).
6. To show it on the homepage under **Featured Pieces**, click the star next to the product in **Products → All Products**.
7. Click **Publish**.

**Edit prices, photos or descriptions:** **Products → All Products** → click the product → change → **Update**.

**Sizes and colours:**
- To add a new size or colour, go to **Products → Attributes → Configure terms**.
- Each colour has a **Swatch colour** picker. This is the colour circle customers tap.
- Sizes show in the order you drag them into on that screen.

**Stock and sold-out items:**
- Stock goes down automatically when an order is placed.
- When a size or colour reaches 0, it is crossed out and can't be chosen.
- When every variation is at 0, the product shows **Sold out** (plus an **Ask on WhatsApp** button once your number is set).
- To mark something sold out by hand, set its stock to 0.
- To stop selling a product, set it to **Draft** or move it to **Trash**.

> **Shared stock?** If the same pieces are sold in the shops and online, agree who updates the website stock after in-store sales, so you don't sell something twice.

## 3. Orders

- **WooCommerce → Orders** lists every order. Click one to see the customer, items (with size and colour), delivery method and the **Pickup branch** if they chose store pickup.
- **Payment status:** *Processing* means paid and ready to prepare. *Pending payment* means the customer didn't finish paying. *On hold* means waiting for a manual check (e.g. bank transfer).
- **Update fulfilment:** change the status to **Completed** once delivered or collected, then click **Update**. The customer is emailed automatically.
- **Refunds:** open the order → **Refund** → enter the amount → refund via the payment gateway (Paystack) or manually.
- **"Dispatched" and "Ready for pickup" (optional):** once you've agreed how orders are fulfilled, tick **Use "Dispatched" and "Ready for pickup" order statuses** in **Appearance → Customize → NolaSharon Settings → Shop, delivery & pickup**. Staff can then set those statuses on an order, and the customer's order tracker in My Account shows the extra step. Leave it off until the process is confirmed.

### Customer accounts

- Customers can check out as guests or create an account from **My Account** (name, email, optional phone, password). In their account they see their orders with a progress tracker, can pay for unpaid orders, re-order completed ones, and edit their delivery address and details.
- Settings live in **WooCommerce → Settings → Accounts & Privacy**. Keep "Allow customers to create an account on the My account page" and guest checkout on.
- **Password reset and welcome emails** need working email: set up **WP Mail SMTP** with your email provider before launch, then send a test.
- **Security:** install a login-protection plugin (**Wordfence** or **Limit Login Attempts Reloaded**) so repeated wrong passwords are blocked.

## 4. Branches and store locations

**Branches** in the left menu holds one entry per store. Each branch has its own tag (e.g. "Branch 1"), area, address, nearest landmark, phone, opening hours (one row per day), Google Maps link, map coordinates, pickup settings, status (Open / Opening soon / Temporarily closed) and opening date. The list shows which branches are still missing details.

- Fill in a branch and click **Publish**. It then appears on the **Store Locations** page, the homepage spotlight, the footer, the Contact page and (if pickup is on) at checkout.
- **Order** (in the "Page Attributes" box) sets the display order.
- **Area** groups branches under the filter tabs on Store Locations (tabs appear once there are two or more areas).
- **Opening hours:** fill in opens/closes for each day, tick **Closed** for days you're shut, leave a day empty if not confirmed. The page shows "Open now · closes 7:00pm" in Lagos time. Add a note like "Public holiday hours may vary" if needed.
- **New branch** (used for Ogudu) adds the "New" pill and the "Now also in…" spotlight, which always says the Ikeja stores remain open. With status **Opening soon**, the opening date shows once filled in.
- **Map:** in Google Maps, right-click the store and click the coordinates to copy them (latitude first), and use **Share → Copy link** for the Google Maps link. Branches with coordinates get a pin on the map; the map only appears once at least one branch has them. **Get directions** uses the Google Maps link, or the coordinates.
- **Photo:** the branch's **Featured image** is used in the spotlight.
- Empty fields are simply hidden on the site.

## 5. Homepage, banner and site settings

**Appearance → Customize → NolaSharon Settings**:

- **Announcement bar:** the thin strip at the very top, e.g. the Ogudu opening or a confirmed offer. Leave it empty to hide it.
- **Homepage hero:** main image, headline, text and the two buttons.
- **Brand introduction:** your short "about us" paragraph and photo.
- **Customer trust strip:** a line each for payment and delivery, once confirmed.
- **Contact & social:** phone, **WhatsApp number** (the WhatsApp buttons only appear once this is filled in), email, customer service hours, Instagram, Facebook and TikTok.
- **Shop, delivery & pickup:** the delivery and returns notes shown on product pages, **Enable store pickup**, the optional order statuses (see Orders), and how many days a product counts as "New".
- **Sign-in page:** the campaign photo beside the sign-in form.

**"Shop Our Collection" on the homepage** shows up to four product categories. Go to **Products → Categories → Edit** on a category: tick **Show in "Shop Our Collection"**, set **Homepage order** (1 is the big tile), pick a **Tile colour**, add an optional short **Tile subtitle**, and set the category **Thumbnail** (the tilted photo). If no category is ticked, the main categories show.

To upload your logo, go to **Appearance → Customize → Site Identity → Logo**.

**Pages** (About Us, FAQs, policies): **Pages → All Pages** → click the page → edit → **Update**. Yellow "To confirm" notes are only visible to logged-in staff. Replace them with the real wording; customers never see them.

## 6. Delivery and store pickup

- **Delivery fees:** **WooCommerce → Settings → Shipping**. Click a zone (e.g. *Lagos*) → edit each rate → set the cost and remove "[[CONFIRM: fee]]" from the name.
- **Area note on the delivery cards:** each flat rate also has a **Checkout note (NolaSharon)** field, shown under its name at checkout (e.g. "Ikeja, Ogudu, Yaba, Surulere…"). Leave it empty to hide it.
- **Terms checkbox:** checkout asks customers to accept the Terms & Conditions and the Returns & Exchange Policy. The Terms page is set under **WooCommerce → Settings → Advanced → Terms and conditions**.
- **Store pickup** stays off until you're ready. To turn it on:
  1. Tick **Pickup available at this branch** on each branch that offers it, and add **Pickup instructions**.
  2. Tick **Enable store pickup at checkout** in Customize → NolaSharon Settings → Shop, delivery & pickup.

  Pickup is free and offered for every state. Customers can then choose a branch at checkout. The branch appears on the order, emails and confirmation page.

## 7. Payments (Paystack)

- **WooCommerce → Settings → Payments → Paystack.** Paste your **test** keys first and place a test order. Then switch to **live** keys at launch.
- Keys come from your own Paystack dashboard. Never send them by email or WhatsApp; enter them yourselves or share them securely.
- The website never stores card details; Paystack handles them.

## 8. Discount codes (if you use them)

**Marketing → Coupons → Add coupon**: set the code, discount and expiry. Only publish offers that are approved. The coupon box on the cart and checkout is hidden for now; tell us when you want discount codes and we'll switch it on.

## 9. Reviews

Customer reviews are held for approval. Go to **Products → Reviews** and approve or trash each one. Never post reviews that customers didn't write.

## 10. Backups, updates and renewals

- **Backups:** daily off-site backups via [[CONFIRM: host backups or UpdraftPlus]]. To restore, [[CONFIRM: restore steps for the chosen host]].
- **Updates:** **Dashboard → Updates** about once a month. Back up first.
- **Renewals:** domain [[CONFIRM: registrar and renewal date]], hosting [[CONFIRM: host and renewal date]], paid plugins [[CONFIRM: none so far]]. All accounts should be in NolaSharon's name.

---

## Still to send us

Logo · brand story and short description · product photos, names, prices, sizes, colours, stock, care details · final categories · phone, WhatsApp, email, service hours, social links · each Ikeja branch and the Ogudu branch (address, landmark, hours, phone, map, pickup yes/no, Ogudu opening date) · payment gateway and methods · delivery areas, fees and timelines · delivery, returns, exchange, refund, privacy and terms wording · any launch offer · domain and hosting choice · whether you want a contact form plugin, newsletter or coupons.
