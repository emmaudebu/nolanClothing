# Local seed scripts

For a local WordPress Studio site only. They create the pages, categories, store settings,
placeholder shipping zones and `[SAMPLE]` products described in `CLAUDE.md`.

```bash
mkdir -p ns-tmp && cp tools/seed-*.php ns-tmp/   # inside the Studio site folder
studio wp eval-file ns-tmp/seed-content.php
studio wp eval-file ns-tmp/seed-products.php      # needs the placeholder images in the media library
rm -rf ns-tmp
```

Never run these on the live site. Delete every `[SAMPLE]` product and the "Sample" category before launch.
