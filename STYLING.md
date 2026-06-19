# Little Merlins — Theme Styling

The pastel "natural" look for the shop is delivered as a CSS override for the
PrestaShop **Classic** theme, kept in version control here:

    themes/classic/assets/css/custom.css

`docker-compose.yml` bind-mounts this file read-only into the running container at
`/var/www/html/themes/classic/assets/css/custom.css`, which the Classic theme loads
automatically after its own stylesheet.

## Apply / update

    git pull
    docker compose up -d        # recreates the prestashop container with the mount

Then clear PrestaShop's cache so the CSS is served fresh:
Back office → Advanced Parameters → Performance → **Clear cache**
(or temporarily turn off "Smart cache for CSS" / CCC while iterating).

## Palette
- Cream / sand backgrounds  (#FAF6EF / #F2E9DC)
- Soft sage primary         (#A7C2A3 → hover #7FA37A)
- Dusty rose accent         (#E7C7C2)
- Sky blue accent           (#BCD6E0)
- Warm muted brown text     (#6B5A4E, headings #4A3F37)

Inspiration: Fjällräven, Moulin/Moalie, Under the Nile, Bears for Humanity.
