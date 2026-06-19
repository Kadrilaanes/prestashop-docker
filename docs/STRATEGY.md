# Little Merlins — Site Improvement Strategy

*Natural-fiber soft toys for babies & children. Sustainable, fair trade, locally produced.*

Prepared June 20, 2026 · Based on a crawl of the live shop at the current Cloudflare tunnel URL.

---

## 1. Where the shop stands today

The foundations are genuinely good. The store is already branded as **Little Merlins**, has a clear value proposition ("Soft toys for little ones, naturally"), a real logo, clean SEO metadata, and five well-written products in a single **Soft Toys** category: Bunny Blossom (€34.90), Squeaky Lamb Security Blanket (€29.50), Pip the Panda (€36.00), Ollie the Organic Owl (€39.50), and Finnegan the Fox (€42.00). Product copy is strong — each toy has a sensory-development angle, an explicit list of sustainable features (undyed organic cotton, corn-fiber stuffing, seed-paper hang tags, plastic-free packaging), stock levels, and reference codes. The custom CMS pages (About, Sustainability, Shipping & Returns, Contact) already exist and carry the brand story.

What lets it down is that it is still wearing the **stock PrestaShop "Classic" theme** with several pieces of demo scaffolding left in place. Visually it reads as a generic default install rather than a considered, pastel, natural-toy brand. The gap between the quality of the content and the quality of the presentation is the single biggest opportunity.

## 2. What's actively hurting the shop right now

The most damaging items are the leftover demo placeholders, because they make a real store look unfinished:

- **Lorem ipsum "Custom Text Block"** on the homepage ("Lorem ipsum dolor sit amet consectetur…").
- **"Sample 1 / Sample 2 / Sample 3" link blocks** ("EXCEPTEUR OCCAECAT…") still pointing to prestashop-project.org.
- A **generic red "sale70.png" banner** from the default ps_banner module that has nothing to do with the brand.
- **Reassurance block not configured** — product pages literally show "Security policy (edit with the Customer Reassurance module)", "Delivery policy (edit…)", "Return policy (edit…)".
- **Store address inconsistency** — the footer and install country say **United Kingdom / GB**, while the brand promises "Designed and made in **Europe**." Pick one and make it coherent.
- **Default newsletter and footer copy** untouched.

None of these require design work; they are content/config cleanups and should be done first because they are quick and high-impact.

## 3. The styling direction

The brand references — Fjällräven, Moulin/Moalie, Under the Nile, Bears for Humanity — share a coherent visual language: muted, natural, earthy-but-soft palettes; lots of warm off-white space; rounded, friendly forms; understated typography; and photography that feels tactile and organic rather than glossy and commercial. Translated into a system:

**Palette (pastel-natural):** warm cream/sand backgrounds, soft sage green as the primary, dusty rose and sky-blue as secondary accents, and a warm muted brown for text and headings instead of hard black. Avoid pure white, pure black, and any saturated "web" colours.

**Form & feel:** generously rounded corners, soft diffuse shadows, comfortable spacing, larger touch targets. Nothing sharp or corporate.

**Typography:** a warm, slightly characterful heading face paired with a clean, highly legible body face. Headings in the muted brown, never pure black.

**Imagery:** natural light, neutral linen/wood backdrops, babies/hands interacting with the toys to convey texture and scale. (Asset creation is a follow-up; the CSS below makes the existing photos sit in a softer frame.)

A ready-to-apply `custom.css` implementing this palette and feel is included in the repo (see section 6).

## 4. Feature & content roadmap

**Tier 1 — finish the storefront (this week).**
Remove the three demo placeholders, configure the reassurance block with real "100% natural fibers / Fair-trade / Free EU shipping over €50 / 30-day returns" messaging, fix the country/address inconsistency, replace the sale banner with a brand hero, and apply the pastel theme. Set proper homepage copy in place of the Lorem ipsum block.

**Tier 2 — merchandising & trust.**
Add subcategories so the catalogue can grow (e.g. *Comforters & Loveys*, *Plush Animals*, *Teethers*, *Nursery Decor*). Add a few product reviews/testimonials (the review system is enabled but empty). Add an age/material filter. Introduce a small range of richer product photos and lifestyle shots. Add a visible "Free EU shipping over €50" bar.

**Tier 3 — growth & differentiation.**
Sustainability storytelling as a feature, not just a page: per-product "impact" badges, a short artisan/fair-trade story block, optional gift-wrap, a wishlist/registry for baby showers, and a newsletter incentive (e.g. "10% off your first order"). Configure real payment and shipping methods and the legal/GDPR pages before going live commercially.

## 5. SEO, performance & housekeeping

Metadata is already solid. Before any public launch, move off the temporary `trycloudflare.com` tunnel to a stable domain (the tunnel URL changes on restart and is bad for SEO and trust). Generate a sitemap, set canonical to the real domain, compress product imagery, and complete the legal pages (Legal Notice, Terms, Privacy/GDPR) which currently still carry default text. Switch `PS_DEV_MODE` off in production (already 0) and keep the admin behind its obfuscated folder (already done).

## 6. How styling gets applied (important — architectural note)

Your PrestaShop install lives entirely inside the Docker named volume `prestashop_data`; the theme files are **not** in the GitHub repo or in any folder I can edit directly. So there are two real ways to apply the pastel theme:

- **Repo + rebuild (version-controlled, recommended):** the `custom.css` is committed to the repo at `themes/classic/assets/css/custom.css`, and `docker-compose.yml` bind-mounts it into the container. You pull and run `docker compose up -d`, and the styling is live and reproducible on every rebuild.
- **Back office (no rebuild, faster to preview):** I drive the admin panel via the browser to upload the logo/favicon, swap the banner, configure the reassurance block, and remove the demo modules. Custom CSS still needs the file route above unless a custom-CSS module is installed.

The cleanups in section 2 are mostly back-office actions; the visual theme is best delivered through the repo route.

## 7. Suggested order of operations

1. Apply the pastel `custom.css` (repo route) — instant visual lift.
2. Remove the three demo placeholders and the sale banner (back office).
3. Configure the reassurance block and fix the country/address.
4. Replace homepage Lorem ipsum with real brand copy.
5. Add subcategories and seed a few reviews.
6. Move to a permanent domain and finish legal pages before public launch.
