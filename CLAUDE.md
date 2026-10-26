# NolaSharon Clothing: WooCommerce website

This is the build brief for the NolaSharon Clothing online store. It is based on the client's
"E-commerce Website Design & Development Brief" (prepared by The Content Geek) plus decisions
agreed with Ifeanyi (Umehaa agency, the developer). **Read this whole file before writing code.**
If this file and an instruction in chat conflict, ask.

---

## 0. Ground rules (from the client brief; do not break these)

1. **Never invent business facts.** Addresses, opening hours, phone numbers, opening dates, prices,
   delivery fees, delivery times, policies, promotions, product specs, reviews and "bestseller"
   labels must come from the client. Where a fact is missing, use a placeholder (see rule 2).
2. **Placeholder convention:** write missing facts as `[[CONFIRM: what is needed]]`, for example
   `[[CONFIRM: Ogudu opening hours]]`. The launch check `grep -rn "\[\[CONFIRM" .` must return
   nothing before go-live. Where possible, make placeholder content **editable in wp-admin** and
   **hide it on the front end when empty** instead of showing the placeholder to customers.
3. **Ogudu is an additional branch, not a relocation.** Every place that mentions Ogudu must make
   clear that the existing Ikeja locations remain open. Never write "moved", "relocated" or "new home".
4. **No fake social proof.** No testimonials, review counts, star ratings or "bestseller" badges
   unless the client supplies or approves them.
5. **The WhatsApp button only appears once a verified number is entered in settings.** With no
   number, render nothing.
6. **Marketing claims** (quality, affordability, delivery speed, offers) must be approved by the
   client. Keep draft copy neutral and mark it `<!-- DRAFT: client to approve -->` in templates or
   as a note in page content.
7. Collect only the customer data needed to process orders.

---

## 1. Project summary

- **Business:** NolaSharon Clothing, a Lagos fashion retailer selling clothing (and accessories, to be
  confirmed) for women and men.
- **Locations:** existing **Ikeja** locations (more than one; count and details to be confirmed) and a
  **new store opening in Ogudu, Lagos**.
- **Goal:** a professional, mobile-first online store where customers browse products, see prices,
  choose size and colour, add to cart, pay online, and choose **delivery or store pickup**, while
  promoting the Ogudu branch and keeping Ikeja visible.
- **Audience:** shoppers in Ogudu, Ikeja and wider Lagos; people who prefer shopping online;
  returning customers checking stock before visiting a store; anyone within the delivery areas.
- **Tone of voice:** friendly, welcoming, stylish, confident, clear, customer-focused, professional
  without being stiff.
- **CTAs to use:** Shop Now · Explore Our Collection · View New Arrivals · Add to Cart ·
  Proceed to Checkout · Visit Our Store · Contact Us.

### Decisions (agreed with Ifeanyi)
| Topic | Decision |
|---|---|
| Platform | **WordPress + WooCommerce**, custom lightweight classic theme `nolasharon` (PHP templates, one CSS file, minimal vanilla JS, no page builder). Client still needs to sign off the platform. |
| Made-to-fit / custom measurements | **Dropped.** Standard sizes and colours only (WooCommerce variable products). |
| Market and currency | **Nigeria only at launch, prices in ₦ (NGN).** No currency switcher. Keep templates currency-agnostic (always use `wc_price()` and WooCommerce price hooks, never hard-coded "₦") so a multi-currency plugin and international shipping can be added later. |
| Payment gateway | **Not decided.** Plan for **Paystack** (cards, bank transfer, USSD). Flutterwave is the alternative. Never hard-code keys; the client owns the account. |
| Branch details | Not yet supplied. Build the Branch system with placeholders, editable in admin. |
| Hosting / domain | Not yet chosen. Recommend LiteSpeed or managed WooCommerce hosting with free SSL, daily backups and Cloudflare. Domain registered in the client's name. |
| Wishlist (2026-10-23) | **Keep it on.** The theme-native wishlist already built stays live (overrides "not needed" in §9). |
| Cart flow (2026-10-23) | The slide-in panel opened from the header cart is the **bag only** (items, totals, Proceed to Checkout). Checkout happens on the real Checkout page built to §5.4. The Cart page is also styled (§5.3). |
| Sizes and colours (2026-10-23) | **WooCommerce variable products** with `pa_size` / `pa_colour` and per-variation stock. The old per-product size list is retired. |
| Theme file layout (2026-10-23) | **Keep the existing structure** (`style.css` holds all CSS, `assets/js/theme.js`, `inc/*.php`, `template-parts/`). New modules are added as files in it rather than reorganising to the §10 tree. |
| Product gallery (2026-10-23) | Keep the approved Total9ja-style 4-image mosaic with lightbox (swipe row on mobile) instead of main image + thumbnails. |
| Product card hover (2026-10-23) | Rounded 8px border, soft lift, Quick View button; Quick View pop-up shares the product page's buy form. |
| Product gallery (2026-10-23, SHOP-PRODUCT.md) | **Replaces the mosaic decision above:** vertical thumbnails beside a main image, hover zoom, lightbox, scroll-snap carousel with dots on mobile. |
| Product card (2026-10-23, SHOP-PRODUCT.md) | Card restyled to SHOP-PRODUCT.md §2.4 (18px radius, badges, colour dots, hover image swap) but **Quick View stays** as the hover button. |
| Header (2026-10-23, SHOP-PRODUCT.md) | **Light/dark theme toggle and dark mode removed.** Wishlist heart stays (wishlist is on). |
| Reviews (2026-10-23) | **Off** until the client asks for them. |
| Bags and shoes (2026-10-25) | The client **sells bags and shoes**. Top-level categories **Bags** and **Shoes** (shoes may also sit in Women/Men). Shoes use a separate global attribute **Shoe size** (`pa_shoe-size`, demo EU 36–45 until the client confirms the range) with size buttons, size guide and shop filter; bags vary by **Colour** only. Extra Details fields: **Dimensions** and **Features**. `[[CONFIRM: shoe size range, Shoes size chart, bag/shoe product details]]` |
| Delivery & returns policy (2026-10-26) | **Client policy received** and applied (Delivery & Shipping and Returns & Exchange pages, FAQs, snippets; `tools/seed-client-policies.php`). Store pickup is **on** (free, the two Ikeja stores, ready within 24 hrs, bring Order ID). **International delivery** is mentioned and arranged by contacting the store; **online checkout stays Nigeria-only**. Checkout delivery fees stay placeholders until the client gives an exact fee per area: `[[CONFIRM: exact delivery fee per zone]]` |

---

## 2. Design system

The client brief says the palette must be based on the approved logo and signed off before full
implementation. The values below come from our approved homepage mockup and are **provisional**:
define them as CSS custom properties in one place so they can be swapped when the logo arrives.

**Typography: two families maximum (client requirement)**
- **Playfair Display** 400/500/600: logo placeholder, section headings, product names.
- **Poppins** 400/500/600/700: body, navigation, buttons, prices, forms, hero headline.
- (The script font from the earlier mockup is **removed** to meet the two-font rule.)
- Load from Google Fonts with `display=swap`, or self-host for speed.
- Clear hierarchy: headings ≠ body ≠ price ≠ product details. Body text 16px minimum on mobile.

**Colours (provisional tokens)**
```css
:root{
  --ns-text:#111111; --ns-text-2:#444444; --ns-text-3:#555555;
  --ns-bg:#FFFFFF; --ns-bg-soft:#F7F7F8; --ns-placeholder:#ECECEC;
  --ns-brand:#8C6448;      /* warm brown: hero, accents */
  --ns-brand-dark:#2B1B12; /* primary buttons */
  --ns-sale:#C62828; --ns-badge:#D93B3B;
  --ns-footer:#000000; --ns-whatsapp:#1F8F4E;
  --ns-success:#2E7D32; --ns-focus:#1A56DB;
}
```
All text must meet 4.5:1 contrast. Never show status (sold out, error) by colour alone; always add text.

**Look and feel:** modern, elegant, clean fashion retail, similar in layout to total9ja.com. Product
photos come first and decoration second. White space, portrait product images (4:5), serif headings,
rounded-pill buttons, sticky white header, black footer.

**Logo:** until the client supplies a high-res logo, use a text logo: "NolaSharon" (Playfair 600)
with "CLOTHING" beneath (Poppins 600, ~10px, letter-spacing .42em, `--ns-brand`). Support
`custom-logo` so the real logo can be uploaded in the Customizer.

**Mockup reference** (private): https://claude.ai/artifact/DfmTDDiSMxEpaTDkG83BYu. Follow its layout,
but where it conflicts with this file (made-to-fit badges, ₦/$/£, script font, "Wear your story"
copy), **this file wins**.

**Images:** `assets/img/*.svg` are illustration placeholders (wrap-dress, agbada, two-piece, kaftan,
bubu, occasion-gown, senator-set, adire-pants, hero-figure). Use them only for demo content. All live
images must be supplied or approved by the client. Products need multiple angles where available,
consistent cropping, and WebP plus responsive `srcset`.

---

## 3. Sitemap

```
Home
Shop (all products)
  └ Category pages (e.g. Women, Men, Accessories: [[CONFIRM: final categories]])
      └ Single product
Cart
Checkout → Order confirmation (thank-you)
My Account (optional: guest checkout is ON)
About Us
Store Locations
Contact Us
FAQs
Delivery & Shipping Policy
Returns & Exchange Policy
Privacy Policy
Terms & Conditions
404 page
```

**Header navigation:** Logo · Home · Shop · Categories ▾ (dropdown of product categories) ·
About Us · Store Locations · Contact Us · search (field on desktop, icon on mobile) · cart icon with
item count. Mobile: logo, search icon, cart, hamburger opening a full-height menu. Keep it simple.

---

## 4. Homepage (sections in order)

| # | Section | Content and rules |
|---|---|---|
| A | **Announcement bar** | Thin dark bar above the header. Text and link set in the Customizer. **Hidden when empty.** Intended uses: Ogudu opening, approved offers, new arrivals, delivery updates. Confirmed info only. Dismissible (remember with `localStorage`, wrapped in try/catch). |
| B | **Header** | As in §3. Sticky. |
| C | **Hero** | Full-width banner, background `--ns-brand`. Campaign image (Customizer, falling back to `hero-figure.svg`). Headline **"Your Style, Your Way."** Supporting text: **"Discover fashion pieces to complement your style and shop the NolaSharon Clothing collection."** Button **Shop Now** → /shop. Optional second button **Visit Our Store** → /store-locations. All editable in the Customizer. Faint oversized "NolaSharon" watermark behind is optional, decorative and `aria-hidden`. |
| D | **Featured categories** | Category cards (image plus white name bar) pulled from **WooCommerce product categories** flagged "Show on homepage" (term meta) or the top-level categories. Use real categories only. Heading "Shop Our Collection", link "Explore Our Collection". |
| E | **New Arrivals** | Latest products (8 on desktop, 4 columns; 2 columns on mobile). Card: image, name, price (sale price shown as strikethrough plus sale price), available colours as small swatches if applicable, link to the product. Heading "New Arrivals", button **View New Arrivals** → shop sorted by date. Hide the section if there are no products. |
| F | **Featured products** | Products marked **Featured** (the WooCommerce star). Heading "Featured Pieces". **Never label as "Bestsellers"** unless the client confirms. Hide if none are featured. |
| G | **Brand introduction** | Short intro with an image and a link to About Us. Copy: `[[CONFIRM: short brand description]]`, editable as page content or in the Customizer. |
| H | **Store spotlight: Ogudu** | "Now also in Ogudu". Image, short text, address, opening date and **Get Directions** / **Visit Our Store** buttons, pulled from the Ogudu Branch post. **Must say the Ikeja stores remain open**, e.g. "Our Ikeja stores remain open. Ogudu is our newest location." Show "Opening soon" with the date only if a date is set; hide the address if empty. Under it, a compact list of all branches. |
| I | **Customer trust strip** | 3–4 items, each shown only when the related setting is filled: Payment methods ("Secure payment by card, transfer and USSD", once confirmed) · Delivery ("Delivery across [[CONFIRM: delivery areas]]") · Store pickup (if enabled) · Customer support (WhatsApp/phone). Reviews only when approved real ones exist. |
| J | **Footer** | Black. Logo plus a short line; columns: **Shop** (Shop, categories) · **Help** (FAQs, Delivery Policy, Returns & Exchange, Track/Contact) · **Visit Us** (each branch name plus area, linking to Store Locations) · **Contact** (phone `tel:`, WhatsApp, email `mailto:`, Instagram, Facebook, TikTok if supplied). Bottom bar: © year NolaSharon Clothing · Privacy Policy · Terms & Conditions. Payment logos only after the gateway is confirmed. |
| – | **Floating WhatsApp button** | Bottom-right on all pages, `https://wa.me/<number>?text=<prefilled>`. **Only renders when a number is set.** On product pages, prefill "Hi, I'm interested in {product name} ({url})". |

---

## 5. Page-by-page requirements

### 5.1 Shop and category pages
- Grid of products: image, name, **₦ price**, availability ("Sold out" badge in text), link to the product.
- Category pages: category name, optional short intro (category description), the products.
- **Filters** (only those relevant to the real catalogue): category, price range, size, colour,
  availability (in stock). Use WooCommerce attribute filtering (block filters or lightweight custom
  filters); avoid heavy filter plugins. On mobile, filters open in a slide-in panel.
- **Sorting:** latest, price low→high, price high→low, popularity (only if meaningful).
- **Pagination** or a "Load more" button (prefer pagination for SEO, ~24 per page).
- Breadcrumbs.

### 5.2 Single product page
In order: breadcrumbs · image gallery (main image plus thumbnails, swipe on mobile, zoom/lightbox) ·
name (H1) · price (with sale price) · short description · **colour** selector (swatches) ·
**size** selector (buttons) · **Size guide** link opening an accessible modal (content from a
"Size Guide" page or per-category size chart; `[[CONFIRM: size charts]]`) · **stock status** text ·
quantity selector · **Add to Cart** (full-width on mobile, sticky add-to-cart bar on mobile scroll) ·
WhatsApp "Ask about this item" (if a number is set) · tabs/accordions: **Description**,
**Details** (fabric/material, fit, care instructions: fields shown only when filled),
**Delivery information** (pulled from a global setting/snippet), **Returns & exchanges** (global
snippet linking to the policy) · **Related products**.
- Unavailable variants must be **visibly disabled and not selectable** (strike-through swatch plus
  "Sold out" text) unless backorders are enabled for that product.
- When the whole product is out of stock: replace Add to Cart with "Sold out" plus an optional "Ask on
  WhatsApp" button.

### 5.3 Cart
Product image, name, selected size/colour, unit price, quantity (+/− and input), line subtotal,
remove item, update quantities without a page reload (WooCommerce cart fragments/AJAX is fine), delivery
fee info or estimate, total payable, **Proceed to Checkout**, "Continue shopping" link. Empty-cart
state with a **Shop Now** button.

### 5.4 Checkout
- **Guest checkout ON**; account creation optional.
- Fields (only what is needed): full name (one field, or first and last), email, phone (required:
  Nigerian format), delivery address, **state** and **city/area**, delivery instructions (optional),
  **Delivery or Store Pickup** choice, payment method, order summary.
- Remove unneeded default fields: company, address line 2 (optional), postcode (hide for NG).
- If **Store Pickup** is chosen: hide address fields and show a **branch selector** listing only
  branches with pickup enabled, plus pickup instructions.
- Show the final amount before payment. Clear inline error messages. Mobile-friendly input types
  (`tel`, `email`), labels always visible.

### 5.5 Order confirmation (thank-you page) and emails
Order number, products and quantities, amount paid / payment status, delivery or pickup details
(chosen branch, address, hours), expected fulfilment info (only if confirmed), support contact
(phone/WhatsApp/email). Style the WooCommerce emails with the brand (logo, colours) and add the
pickup branch details to the order emails and admin order screen.

### 5.6 About Us
Sections: brand story · what we offer · our values · the shopping experience · our stores (links
to Store Locations, mentioning Ikeja and Ogudu). All copy is `[[CONFIRM: brand story]]`. A neutral
structure with draft headings is fine; mark drafts.

### 5.7 Store Locations (important)
- One listing **per branch**: branch name, full address, nearest landmark, opening hours,
  contact number (`tel:` link), **Get Directions** (Google Maps link) and optional embedded map,
  pickup availability (Yes/No).
- **Ogudu** card gets a "New" badge and "Opening [date]" or "Now open". The page intro states:
  "Visit us at any of our Lagos stores. Our Ikeja locations remain open, and Ogudu is our newest
  branch." `<!-- DRAFT: client to approve -->`
- **Do not assume** Ogudu shares Ikeja's hours, phone or pickup setup; every branch has its own fields.
- Add `LocalBusiness`/`ClothingStore` JSON-LD **per branch**, output only for branches whose address
  is confirmed.

**Implementation: Branch custom post type** (so the client edits branches without a developer):
- CPT `ns_branch` (public: false; shown via the Store Locations template and homepage/footer).
- Fields (native meta boxes, no ACF dependency): `area` (e.g. Ikeja, Ogudu), `address`,
  `landmark`, `phone`, `whatsapp` (optional), `hours` (repeatable day/time rows or a textarea),
  `map_url`, `map_embed` (optional), `lat`/`lng` (optional), `pickup_enabled` (bool),
  `pickup_instructions`, `status` (open / opening_soon / temporarily_closed), `opening_date`,
  `is_new` (bool), `order` (menu order).
- Front end: hide any empty field; never print `[[CONFIRM` text to visitors (show nothing instead).
- **Seed data on theme activation (drafts):** "NolaSharon Ikeja (Branch 1)", "NolaSharon Ikeja
  (Branch 2)" and "NolaSharon Ogudu" (status opening_soon, is_new = true), all with empty fields and an
  admin notice: "Branch details not confirmed". The client confirms how many Ikeja branches exist:
  `[[CONFIRM: number of Ikeja branches]]`.
- Store pickup at checkout reads from branches with `pickup_enabled = true`. **Pickup is disabled
  site-wide until the client confirms** (theme setting `ns_pickup_enabled`, default false).

### 5.8 Contact Us
Phone (`tel:`), WhatsApp, email (`mailto:`), Instagram, Facebook (and TikTok if supplied), customer
service hours, contact form (WPForms Lite or Fluent Forms with spam protection), and a short list of
branches linking to Store Locations. Every value comes from theme settings; hide the row when empty.

### 5.9 FAQs
Accordion (`<details>`/`<summary>` or accessible buttons) plus `FAQPage` JSON-LD. Questions:
How do I place an order? · What payment methods do you accept? · Where do you deliver? · How much does
delivery cost? · How long does delivery take? · Can I collect my order from a store? · How do I choose my
size? · Can I exchange a product? · What happens if a product is out of stock? · How do I contact customer
support? **Answers must reflect real policy.** Draft only the purely procedural ones (how to place an
order, how to contact us); the rest are `[[CONFIRM: …]]`.

### 5.10 Policy pages: structure only, content from the client
- **Delivery & Shipping:** delivery locations, charges, estimated timelines, order processing time,
  dispatch procedure, how customers get updates, failed delivery attempts, pickup availability.
- **Returns & Exchange:** whether returns and exchanges are accepted, timeframe, product condition,
  exclusions, how to request, who pays return delivery, how refunds work.
- **Privacy Policy:** what data is collected, how it is used, stored and protected, payment processor,
  analytics, marketing, customer rights, contact. It must match the real plugins and analytics in use.
- **Terms & Conditions:** orders, pricing, availability, payment, delivery, cancellations,
  returns/exchanges, customer responsibilities.
Create each page with H2 section headings and `[[CONFIRM: …]]` under each. **Do not write policy
terms.** The WordPress privacy-policy template may be used as a starting point and flagged for
client review.

### 5.11 404 page
Friendly message, search box, buttons to Shop and Home.

---

## 6. Products, catalogue and inventory

- Use **variable products** with global attributes **Size** (`pa_size`) and **Colour** (`pa_colour`,
  with a hex term meta for swatches). Set the size list once the client confirms it.
- Product fields to support: name, SKU, category, description, short description, regular price,
  sale price, images (gallery), sizes, colours, stock quantity per variation, stock status,
  **fabric/material**, **fit**, **care instructions** (custom product meta, shown in Details),
  SEO title and description (via the SEO plugin).
- Stock management ON at variation level; "Sold out" shown automatically; backorders off by default.
- **Inventory question for the client:** is online stock separate from in-store stock, or shared?
  If shared, agree a procedure (e.g. a dedicated online allocation, or staff update stock on in-store
  sales) to avoid overselling. `[[CONFIRM: inventory procedure]]`
- Badges: **"New"** (product less than 30 days old, configurable), **"Sale"** (on sale), **"Sold out"**.
  No "Bestseller" badge.
- Demo content: create about 8 sample products using the illustration SVGs, marked as drafts or
  in a "Sample" category, all titled with a `[SAMPLE]` prefix, and delete them before launch.

---

## 7. Payments, delivery and pickup

**Payments**
- Install **Paystack for WooCommerce** (to be confirmed); support card, bank transfer and USSD as the
  gateway allows. Test mode first; live keys entered by the client or securely at launch.
- The site must never store raw card data. Show clear success/failure messages; correct order
  statuses (Pending → Processing → Completed); refunds documented for the client.
- Optional: "Direct bank transfer (BACS)" with manual verification, only if the client wants it.
  `[[CONFIRM: payment methods]]`

**Delivery** (WooCommerce shipping zones; fees to be confirmed)
- Suggested zones: *Lagos – Mainland*, *Lagos – Island*, *Lagos – Outskirts*, *Other Nigerian
  states*, each with flat rates `[[CONFIRM: delivery fees]]`. Restrict selling/shipping countries to
  **Nigeria** for now.
- Never display delivery times the business has not confirmed.
- A global "Delivery information" snippet (theme setting) is reused on product pages, the cart and FAQs.

**Store pickup**
- Use WooCommerce **Local Pickup** (or a custom shipping method `ns_branch_pickup`) with a branch
  selector fed by the Branch CPT. The chosen branch is saved to order meta and shown in admin, emails
  and the thank-you page.
- **Disabled until the client confirms** which branches offer pickup, pickup hours, whether
  customers wait for a "ready for pickup" confirmation, and what ID or order number to present.

**International (later):** keep code ready but **do not enable**. Future: CURCY/WOOCS multi-currency
plus international shipping zones.

---

## 8. Theme settings (Customizer panel "NolaSharon Settings")

Announcement bar text and link · hero image, headline, text, button labels and links · brand intro
text and image · business phone · WhatsApp number (E.164, e.g. 234XXXXXXXXXX) · prefilled WhatsApp
message · email · customer service hours · Instagram / Facebook / TikTok URLs · delivery-information
snippet · returns snippet · pickup enabled (bool) · "New" badge days · YouTube/video URL (optional
section). **Empty means hidden on the front end.**

---

## 9. Plugins (keep the list short and maintained)

| Purpose | Plugin | Cost |
|---|---|---|
| Store | WooCommerce | Free |
| Payments | Paystack for WooCommerce (or Flutterwave) | Free plugin; gateway takes transaction fees |
| SEO + XML sitemap | Rank Math (or Yoast) | Free |
| Forms | WPForms Lite / Fluent Forms | Free |
| Caching | LiteSpeed Cache (LiteSpeed host) / WP Rocket | Free / paid |
| Image optimisation (WebP) | LiteSpeed Cache image optimisation / ShortPixel | Free / freemium |
| Security | Wordfence or Solid Security (basic) | Free |
| Backups | UpdraftPlus (off-site daily) or host backups | Free |
| Email deliverability | WP Mail SMTP (with Brevo/Mailgun/host SMTP) | Free |
| Analytics (if agreed) | Site Kit by Google | Free |

Not needed: wishlist, loyalty, multi-currency, newsletter. **Ask the client** whether they want discount
codes (WooCommerce coupons are built in), a wishlist or a newsletter: `[[CONFIRM: extras]]`. List any
paid items and recurring costs for client approval (brief §8.3).
(Wishlist: kept on, see the decisions table in §1.)

---

## 10. Technical standards

**Theme structure** (reference only; the existing layout is kept, see §1 decisions)
```
nolasharon/
  style.css               (theme header only)
  functions.php           (requires inc/*)
  inc/  setup.php  enqueue.php  customizer.php  woocommerce.php
        branches.php (CPT + meta + seed)  checkout.php (fields + pickup)
        product-meta.php  schema.php  template-tags.php
  assets/css/main.css  assets/js/main.js  assets/img/
  header.php  footer.php  front-page.php  index.php  page.php  404.php  search.php
  page-templates/  store-locations.php  contact.php  faq.php
  template-parts/  hero.php  category-cards.php  product-grid.php  branch-card.php
                   trust-strip.php  store-spotlight.php  whatsapp-button.php
  woocommerce/     (only override templates that need it: archive-product, content-product,
                    single-product parts, cart, checkout/thankyou)
```
- Declare `add_theme_support('woocommerce')`, gallery zoom/lightbox/slider, `custom-logo`,
  `title-tag`, `post-thumbnails`, `html5`, `responsive-embeds`.
- Prefix everything `ns_`. Escape output (`esc_html`, `esc_url`, `esc_attr`), sanitise input, use
  nonces. Translation-ready (`nolasharon` text domain).
- Prefer hooks and filters over template overrides; keep overrides minimal for WooCommerce updates.
- Vanilla JS only (no jQuery dependency in our code), deferred. One CSS file, mobile-first.

**SEO:** unique titles and meta descriptions, clean permalinks (`/shop/`, `/product-category/women/`,
`/product/name/`), one H1 per page, descriptive alt text, XML sitemap, Product schema (WooCommerce
default plus SEO plugin), LocalBusiness schema per confirmed branch, Search Console and Analytics if
agreed. Consistent NAP (name/address/phone) across the site.

**Performance:** WebP plus `srcset`, lazy-load below-the-fold images, preload the hero image and
fonts, no sliders/carousels unless needed, minimal animation, no unnecessary pop-ups, test on a slow
mobile connection. Target Lighthouse mobile ≥ 85.

**Accessibility (WCAG 2.1 AA):** 4.5:1 contrast, visible focus states, keyboard-navigable menu,
filters, modal and accordions; labels on every input; meaningful alt text; error messages in text;
tap targets ≥ 44px; `prefers-reduced-motion` respected.

**Security:** HTTPS, strong admin passwords and 2FA, least-privilege roles (Shop Manager for staff),
remove unused admin accounts, keep core/plugins updated, daily off-site backups plus a documented
restore procedure, restricted access to customer data.

---

## 11. Build order

1. Theme scaffold, design tokens, fonts, header/footer, Customizer settings, WhatsApp button.
2. Branch CPT plus seed drafts, Store Locations template, footer branches, homepage spotlight.
3. Homepage sections A–J.
4. Shop/category archive, product card, filters, sorting, pagination.
5. Single product (variations, swatches, size guide modal, details tabs, sticky add-to-cart on mobile).
6. Cart, checkout customisation (fields, Nigeria-only, pickup branch selector behind a flag),
   thank-you page, branded emails.
7. Content pages: About, Contact, FAQs (plus schema), the four policy pages (structure plus
   placeholders), 404.
8. Sample products (`[SAMPLE]`), shipping zones (placeholder rates), Paystack in test mode.
9. QA against §12, then Lighthouse/accessibility pass.

Work in small steps and commit after each step. If the connection drops, continue from the last
completed step.

---

## 12. Testing checklist (from client brief §15 and §19)

- **Design:** mobile (Android/iPhone), tablet, laptop, desktop; logo correct; fonts and colours
  consistent; nothing overlaps.
- **Navigation:** logo → home; menu, category, footer, store location and contact links work; search works; no 404s.
- **Products:** names, prices, images, sizes, colours and stock correct; sold-out variants cannot be bought.
- **Cart:** add, change quantity, remove; subtotal, delivery and total correct.
- **Checkout:** required-field validation; delivery info; payment options show; success and failure
  handled (Paystack test cards); customer confirmation email and admin new-order email received.
- **Locations:** Ogudu shown as a new additional branch; Ikeja branches visible; addresses, hours,
  phones and map links correct.
- **General:** policy pages live, HTTPS, forms work, images optimised, **no `[[CONFIRM` or `[SAMPLE]`
  left**, no broken links, works in Chrome/Safari/Firefox/Edge, backups configured, admin secure.

---

## 13. Information still needed from the client

**Brand:** high-res logo; brand colours/fonts (if any); brand story; short description; social links;
approved photos.
**Products:** final categories; names, descriptions, prices, sizes, colours, stock, measurements,
SKUs, care instructions, images. Number of products in the initial setup.
**Contact:** official phone, WhatsApp number, email, customer service hours, social accounts.
**Ikeja branches (each):** exact address, landmark, opening hours, phone, map location, pickup (Y/N).
How many Ikeja branches?
**Ogudu branch:** exact address, landmark, confirmed opening date, opening hours, phone, map
location, pickup (Y/N).
**Payments:** gateway choice (Paystack/Flutterwave), methods, account readiness, refund procedure,
manual-payment verification if BACS is used.
**Delivery:** areas, fees, timelines, logistics provider, pickup options, failed-delivery procedure,
customer notification process.
**Policies:** delivery, returns, exchange, cancellation, refund, privacy, terms.
**Promotions:** any launch offer (exact offer, eligibility, dates, exclusions, terms).
**Other:** domain name, hosting choice, whether customers need accounts, extras (coupons, wishlist,
newsletter), inventory procedure (shared vs separate stock).

**All unconfirmed information stays unpublished until approved.**

---

## 14. Handover (client brief §14 and §22)

Write `HANDOVER.md` (and optionally record a short video) covering: logging in, changing passwords,
adding/editing products, images, prices, sizes/colours, stock, marking sold out, viewing orders and
payment status, updating fulfilment status, editing **branches** (addresses/hours), editing the
banner/hero and announcement bar, coupons (if used), analytics, user permissions, backups and restore,
renewals (domain, hosting, paid plugins). All business-critical accounts stay under the client's control.
