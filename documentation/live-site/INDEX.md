# Post Duplicator — live documentation manifest

Local drafts for customer-facing pages on [Metaphor Creations](https://www.metaphorcreations.com). **Not** the GitHub wiki (`wiki/`). Content should stay aligned with `wiki/*.md`; the live site adds layout, blocks, and screenshots.

When updating the plugin, review the matching wiki page and this file, then sync changes to WordPress manually.

## Pages

| Live title | Live URL | Markdown draft | Gutenberg markup | Wiki counterpart | Last synced (YYYY-MM-DD) |
|------------|----------|----------------|------------------|------------------|--------------------------|
| Duplicating Posts | https://www.metaphorcreations.com/article/post-duplicator/general/duplicating-posts/ | [duplicating-posts.md](./duplicating-posts.md) | [duplicating-posts.blocks.html](./duplicating-posts.blocks.html) | [wiki/Duplicating-Posts.md](../../wiki/Duplicating-Posts.md) | — |
| Settings | https://www.metaphorcreations.com/article/post-duplicator/general/settings/ | [settings.md](./settings.md) | [settings.blocks.html](./settings.blocks.html) | [wiki/Settings.md](../../wiki/Settings.md) | — |

Fill in **Last synced** when you copy updates to the live site.

## Conventions

- **Placeholders:** In `.md`, look for `<!-- IMAGE(placeholder): ... -->`. In `.blocks.html`, dashed **Group** blocks with class `doc-image-placeholder` mark the same spots—swap for **Image** blocks on the live site.
- **`*.blocks.html`:** Serialized block markup (spacing via Group `margin` / `blockGap`, wide **Separator**s, **Table** with stripes for comparisons). Paste into the editor **Code editor** (⋮ menu) or set `content.raw` via REST. Remove the top HTML comment before saving if your workflow requires bare blocks only.
- **Markdown (`*.md`):** Human-editable source; use when updating copy before regenerating or hand-editing `.blocks.html`.
- **Theme presets:** Markup uses `var:preset|spacing|*` and `textColor: contrast-2` where helpful. If the theme omits those presets, re-save from the editor or switch colors in **Styles**.

## Cursor

See `.cursor/rules/live-documentation-site.mdc`. Ask to **check live documentation** or **sync documentation pages** when you want a drift review against the wiki and plugin behavior.
