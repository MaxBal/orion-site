---
tags:
  - domain
  - content
---

# News

## Tables

**`site_news`** — the post:

| Field | Note |
|---|---|
| `title`, `summary`, `body` | `summary` is optional |
| `status` | `draft` / `published` |
| `is_pinned` | pinned posts sort first |
| `published_at` | may be `NULL`, then `created_at` is used for ordering |
| `author_account_id` | `LEFT JOIN` on `accounts`, since the author may be deleted |

**`site_news_media`** — attachments, `media_type` of `image` or `video`, with `sort_order`.
This holds the schema's only real `FOREIGN KEY`: `ON DELETE CASCADE` on `news_id`.
Deleting a post removes its media rows automatically — but **not the files on disk**.

## Homepage rendering

`index.php` pulls the 8 most recent published posts:

```sql
WHERE n.status = 'published'
ORDER BY n.is_pinned DESC, COALESCE(n.published_at, n.created_at) DESC, n.id DESC
LIMIT 8
```

The `COALESCE` matters: a draft that was just published may still have an empty `published_at`.

Media is loaded with **one additional query** using `WHERE news_id IN (...)` and grouped in PHP,
to avoid a query per post (N+1).

The first media item becomes the card cover; the rest render as a thumbnail strip below the text.
With no media, `images/news-default-cover.png` is used.

The body is rendered as `nl2br(h($news['body']))`: HTML is escaped, line breaks are preserved.
**HTML in news is not supported** — deliberately.

## Admin panel

The `news` tab. `news.manage` (create/edit) and `news.delete` (remove) permissions.
Form-plus-redirect mode, because of file uploads.

Actions: `save_news`, `delete_news`, `delete_news_media`.
Audit: `news.save`, `news.delete`, `news.media.delete`.

Files land in `uploads/news/`.

> [!warning] Files are never cleaned up
> `ON DELETE CASCADE` clears **database rows**, not files on disk. `uploads/news/` accumulates
> orphans over time. There's no cleanup today — a known gap.

## Translation

News bodies are **not** translated into Ukrainian. They're free-form text written by admins,
and phrase-substituting inside them produces half-Russian, half-Ukrainian paragraphs.
See [[Localization]].

## Related update history

- **`site_updates`** — release history edited from the admin `updates` tab. Entry shape:
  `version`, `name`, `release_date`, `tag`, `intro`, `categories_json`, and draft/published
  status. The original 1.0 entry is seeded once by `ensure_update_history_schema()`.
- **`changelog.php`** reads only published rows, newest release first.
- **`includes/roadmap_data.php`** — the roadmap, bilingual, also in code.

News and updates remain separate content types, but both now live in the database and are
editable from dedicated admin tabs.

Related: [[Database schema]], [[Admin panel]]
