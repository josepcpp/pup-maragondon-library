# PUP Maragondon University Library — Website & CMS

The public website and flat-file content management system for the
Polytechnic University of the Philippines, Maragondon Campus Library.

Built as plain PHP with no framework and no database: all editable content lives
as JSON under `data/`, managed through the admin panel.

---

## Requirements

- PHP 8.0 or newer (uses `match`, nullsafe operators, typed helpers)
- Apache with `mod_rewrite` and `mod_headers`
- The GD extension, for image cropping on upload
- A writable `data/` directory and `assets/uploads/` directory

Runs on XAMPP as-is.

---

## Layout

```
├── index.php              Home
├── about.php              Vision, goals, hours, history
├── services.php           Reader services (tabbed)
├── resources.php          Online databases
├── holdings.php           Collections
├── guidelines.php         Rules and policies
├── programs.php           Programs & events
├── linkages.php           Institutional partnerships
├── survey.php             Satisfaction survey
├── administration.php     Personnel directory
├── contact.php            Contact details and hours
├── 404.php                Not-found page
│
├── includes/              Shared public header and footer
├── style.css              Master public stylesheet
├── script.js              Public behaviour
│
├── admin/                 CMS (session auth, CSRF, rate limiting)
│   ├── includes/          Admin chrome
│   ├── api/               JSON endpoints for the media picker
│   └── style.css          Admin stylesheet
│
├── data/                  Site content as JSON — the CMS writes here
└── assets/                Logos, images, and CMS uploads
```

### Content files

| File | Drives |
|---|---|
| `settings.json` | Site title, hero copy and stats, contact, social, OPAC and survey URLs |
| `about.json` | Vision/goals/mission/objectives, weekly schedule, history timeline |
| `services.json` | Reader-service sections |
| `resources.json` | Online database listings |
| `holdings.json` | Collection categories and counts |
| `guidelines.json` | Rules, grouped into accordion sections |
| `programs.json` | Programs and events |
| `linkages.json` | Partnerships and MOA copy |
| `personnel.json` | Staff directory |
| `arrivals.json` | New arrivals shown on the home page |
| `featured.json` | Featured resource on the home page |

---

## Design system

The public site and the admin panel share one vocabulary, defined as CSS custom
properties at the top of `style.css` and `admin/style.css`.

- **Maroon `#800000`** carries structure — headings, navigation, primary actions.
- **Gold `#ffb71b`** is a single accent, used once per view at most.
- Neutrals are warm paper tones; separation comes from **hairline rules**, not
  shadows or gradients.
- Type is Playfair Display for display sizes, Source Sans 3 for text.
- Motion is one restrained scroll reveal, and it honours
  `prefers-reduced-motion`.

Legacy token names (`--parchment`, `--white`, `--border`, `--shadow-card`, …)
are kept as aliases so inline styles across the PHP pages resolve through the
same system.

Dark mode is a `data-theme="dark"` attribute on `:root`, toggled from the
header and remembered in `localStorage`.

---

## Local setup

1. Place the project under your web root (e.g. `htdocs/library-website1`).
2. Ensure `data/` and `assets/uploads/` are writable by the web server.
3. Visit `/admin/login.php`.

On first run the CMS writes `data/admin_credentials.json` from the defaults in
`admin/config.php`.

> **Change the admin password immediately after the first login**, from
> *Site Settings → Change Password*. The default credentials are documented in
> the admin manual and are not secret. `data/admin_credentials.json` is
> git-ignored and must never be committed.

---

## Notes

- This is a **content management system**, not a library management system.
  Circulation, cataloguing, and patron records live in the separate Koha OPAC
  the site links out to.
- `cms_save()` writes atomically (lock file, temp file, rename) so a failed
  write cannot truncate a content file.
- Uploads are validated by extension *and* MIME type, then re-encoded through GD
  at fixed dimensions per content type.
