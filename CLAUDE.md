# NolaSharon Clothing — WooCommerce site

Project brief and decisions carried over from planning. Read this before building anything.

## The client and the brief
- **Brand:** NolaSharon Clothing. A Nigerian fashion brand selling handmade clothing for women and men.
- **Two ways to buy:** **ready-to-wear** (standard sizes) and **made-to-fit** (the customer submits body measurements).
- **Customers:** local (Nigeria) and international (diaspora: UK, US, Canada and others).
- **Catalogue:** 100+ products at launch.
- **Reference site:** https://www.total9ja.com. Follow its layout and user flow, but use NolaSharon's own branding, copy and images. Do not copy Total9ja's assets or text.
- Built by Ifeanyi (Umehaa agency) for the client.

## Stack (decided)
- **WordPress + WooCommerce.** We chose this over a custom Next.js build because it is faster to deliver, the client can manage it themselves, and handover is easy. A headless Next.js front end can come later if needed.
- **Approach:** a custom classic WooCommerce theme called `nolasharon` (PHP templates + one CSS file + minimal vanilla JS). No page builder. Keep it lightweight and fast.
- **Hosting:** not yet chosen. Recommend LiteSpeed or managed WooCommerce hosting with Cloudflare in front.

## Plugin stack
| Need | Plugin |
|---|---|
| Payments (NGN and international cards) | Paystack for WooCommerce and/or Flutterwave for WooCommerce |
| Multi-currency ₦ / $ / £ with geolocation | CURCY (WooCommerce Multi Currency) or WOOCS |
| Made-to-fit measurement fields | Advanced Product Fields (Extended), or custom fields in the theme (preferred: theme-native) |
| Shipping zones (Lagos, rest of Nigeria, international) | WooCommerce core zones; DHL or flat-rate options |
| Order tracking page | Advanced Shipment Tracking, or a custom "Track Order" page |
| Wishlist | YITH WooCommerce Wishlist or TI Wishlist |
| WhatsApp floating button | Built into the theme (wa.me link); no plugin needed |
| SEO | Rank Math or Yoast |
| Caching / performance | LiteSpeed Cache (on LiteSpeed host) or WP Rocket; images as WebP |
| Forms (Contact) | WPForms Lite / Fluent Forms |

## Design system (from the approved homepage mockup)
- **Fonts (Google Fonts):** Playfair Display 400/500/600 (logo and section headings), Poppins 400–800 (body, UI, hero headline), Allura (script word in the hero).
- **Colours:**
  - Text `#111111`, secondary text `#444444` and `#555555`
  - Page background `#FFFFFF`, light section background `#F7F7F8`, image placeholder `#ECECEC`
  - Hero tone `#8C6448` (warm brown), dark button `#2B1B12`, accent brown `#8C6448` (the "CLOTHING" wordmark)
  - Sale price red `#C62828`, cart badge red `#D93B3B`
  - Footer `#000000` with white text
  - WhatsApp button `#1F8F4E`
- **Logo (text placeholder until the client supplies one):** "NolaSharon" in Playfair Display 600, with "CLOTHING" in tiny Poppins 600 tracked widely (0.42em) underneath.
- **Feel:** clean, white, editorial. Serif headings, portrait product photos, generous spacing. Total9ja-like.

## Homepage sections (in order)
1. **Header (sticky, white):** logo left · nav (Home, Collections, Women, Men, Our Story, Resources ▾) · light/dark theme toggle · grey search box ("Search products...") · cart icon with red count badge. On mobile: logo, search, cart, hamburger.
2. **Hero (full width, background #8C6448):** script "NolaSharon" (Allura), H1 "Wear your story." (Poppins 700, about 66px), outlined pill "NEW COLLECTION", subtext "Handmade ready-to-wear and made-to-fit pieces for women and men, delivered from Nigeria to the world.", buttons **SHOP NOW** (dark) and **GET IT MADE TO FIT** (white). Hero illustration on the right, plus a faint oversized "NolaSharon" watermark behind it.
3. **Shop Our Collections:** serif H2 plus an underlined "View all collections" link. 4 tiles (Women, Men, Adire Edit, Occasion Wear): image with a white name bar at the bottom.
4. **New In (background #F7F7F8):** 4-column grid (2 columns on mobile). Each card: portrait image; a "Ready to ship!" badge (grey, italic) or a "Made to fit" badge (white); centred serif product name; crossed-out regular price, red sale price and "% off". "VIEW ALL PRODUCTS" outlined button below.
5. **Take a walk in our production space:** centred serif H2, a "Read about us" link, and a full-width YouTube embed of the studio.
6. **Footer (black):** logo left; 4 link columns: (Shop Women, Shop Men, Collections) (Our Story, FAQs, Size Guide) (Contact Us, Call Us, Track Order) (Instagram, TikTok, WhatsApp). Bottom bar: "© NolaSharon Clothing 2026 | Theme ☀" and "Privacy Policy | Our Policies".
7. **Floating WhatsApp button** bottom right, on every page.

Mockup (private canvas, desktop + mobile): https://claude.ai/artifact/DfmTDDiSMxEpaTDkG83BYu

## Pages to build
Home · Shop (all products, with filters for category, size, colour, price and ready-to-wear vs made-to-fit) · Collection/category archive · Single product · Cart · Checkout · My Account (saved measurements) · Our Story · FAQs · Size Guide · Contact · Track Order · Privacy Policy · Our Policies (shipping, returns).

## Made-to-fit requirements
- Each product has an option: **Ready-to-wear** (size select XS–3XL) or **Made-to-fit**.
- Made-to-fit shows measurement fields: bust/chest, waist, hips, shoulder, sleeve length, full length, height, plus notes. Show units (inches or cm).
- Measurements are saved to the order (visible in admin order view and emails) and optionally to the customer's account for reuse.
- The size guide opens in a modal from the product page.
- Products have flags such as "ready to ship" or "made-to-fit available"; these drive the card badges.

## Assets
`assets/img/` holds SVG fashion illustrations used as placeholders until a real photoshoot:
hero-figure, wrap-dress, agbada, two-piece, kaftan, bubu, occasion-gown, senator-set, adire-pants.
Replace these with real product photography before launch.

## Still to confirm with the client
Logo files, final colours, real prices, delivery times, WhatsApp number, email, social handles, return policy, and which payment gateway(s) they have accounts with.
