# Local seed scripts

For a local WordPress Studio site only. They create the pages, categories, store settings,
placeholder shipping zones and `[SAMPLE]` products described in `CLAUDE.md`.

```bash
mkdir -p ns-tmp && cp tools/seed-*.php ns-tmp/   # inside the Studio site folder
studio wp eval-file ns-tmp/seed-content.php
studio wp eval-file ns-tmp/seed-products.php      # needs the placeholder images in the media library; deletes ALL products first
cp -R tools/sample-images ns-tmp/
studio wp eval-file ns-tmp/seed-bags-shoes.php    # Bags/Shoes categories, Shoe size terms, 8 [SAMPLE] bags and shoes (safe to re-run)
studio wp eval-file ns-tmp/seed-client-policies.php  # client's delivery & returns policy, FAQs, snippets, pickup on
rm -rf ns-tmp
```

Never run the content/product seeds on the live site. `seed-client-policies.php` and `seed-policy-drafts.php` are written so they can be run there too (`wp eval-file`). Delete every `[SAMPLE]` product and the "Sample" category before launch.
