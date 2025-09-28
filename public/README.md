## AGT Shop

Quickstart:

1. Ensure PHP 8.1+ with SQLite extension is installed.
2. Serve `public/` as your web root (e.g., `php -S 0.0.0.0:8080 -t public`).
3. Run one-time migration: open `/migrate.php` in the browser once.
4. Register a user, list products, add to cart, checkout.

Structure:
- `public/` entry points
- `includes/` core PHP includes
- `assets/css/style.css` site styling
- `data/agt_shop.sqlite` SQLite database
- `uploads/` product thumbnails

