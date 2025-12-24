# ResponseLab

ResponseLab is a lightweight, framework-free PHP app for running A/B tests and collecting feedback. It uses SQLite, Tailwind via CDN, and is designed to run on shared hosting.

## Features
- Admin-authenticated dashboard to create and manage tests.
- Participant view at `/t/{slug}` with variant selection and feedback.
- Anonymous toggle with optional identity capture.
- Edit submissions before expiry via secure edit token (cookie + edit link).
- Public results after expiry (plus admin-only export and tables).
- CSV export for submissions.

## Project Structure
```
/app
  auth.php
  config.php
  db.php
  helpers.php
  submission_repository.php
  test_repository.php
  view.php
/admin
  index.php
  login.php
  setup.php
/assets
  app.js
  styles.css
/data
  app.sqlite (created automatically)
/public
  index.php
/uploads
/views
```

## Local Development
1. Ensure PHP 8+ with SQLite enabled.
2. From the repo root:
   ```bash
   php -S localhost:8000 -t public
   ```
3. Visit `http://localhost:8000/admin/setup.php` to create the first admin.
4. Log in at `http://localhost:8000/admin/login.php`.

> Note: When using the built-in PHP server, `/admin` is not under `/public`. You can instead run `php -S localhost:8000` from the repo root and access `/admin` directly.

## Shared Hosting Deployment
1. Upload the repository contents to your hosting root.
2. Ensure the following directories are writable by PHP:
   - `/data`
   - `/uploads`
3. Update secrets in `app/config.php`:
   - `csrf_key`
   - `token_hmac_key`
   - `ip_hash_salt`
4. Visit `/admin/setup.php` to create the first admin.
5. Remove or protect `/admin/setup.php` after setup.

## Admin Setup
- `GET /admin/setup.php` is available only when no admin exists.
- Once the admin is created, the setup page becomes inaccessible.

## Participant Flow
- Participant link: `/t/{slug}`.
- Users can submit once and edit before expiry using:
  - A secure edit token stored in an HttpOnly cookie (best effort on shared hosting).
  - A one-time edit link: `/t/{slug}?edit=TOKEN`.
- Edit tokens are hashed in the database (never stored raw).
- Without accounts, preventing duplicate submissions is best-effort and relies on tokens/cookies.

## Security Notes & Trade-offs
- CSRF protection is enabled for all POST requests.
- Output is escaped to prevent XSS.
- File uploads validate MIME type and size (default 15MB per file).
- Images are stored in `/uploads/{slug}` with random filenames.
- `/uploads/.htaccess` blocks script execution on Apache.
- IP addresses are not stored directly; an optional salted hash is recorded.
- Cookies are HttpOnly with Lax SameSite; `secure` flag is best-effort (only when HTTPS is detected).

## Assumptions
- You can enable URL rewriting (Apache `.htaccess`) for clean routes.
- SQLite is available on the hosting environment.
- Uploaded image types: JPG/PNG/GIF/WebP.

## Future Enhancements
- Drag-and-drop image reordering.
- Admin roles + audit logs.
- Additional result visualization.
- Email notifications on test expiry.
