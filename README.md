# Portfolio

Personal portfolio site for Dayana Lorza.

## Structure

- `index.html` — current portfolio site
- `css/`, `js/`, `images/`, `resume/` — current site assets
- `archive/` — previous portfolio versions
  - `2018/` — original developer portfolio
  - `2024/` — portfolio refresh

The small 🥚 beside the copyright on the current site links to the archive.

## Local preview

The site can be opened directly in a browser for a static preview. The contact form requires a PHP-capable server and will not send from a `file://` URL or a static-only host.

## Contact form configuration

The contact handler in `contact.php` deliberately contains no personal email address. Configure these environment variables on the hosting server before enabling the form:

```text
PORTFOLIO_CONTACT_RECIPIENT=your-recipient@example.com
PORTFOLIO_CONTACT_FROM=no-reply@example.com
```

Without both values, the form returns a safe “not configured” response.

## Privacy

No direct email address, analytics ID, or macOS metadata is stored in the repository. Local metadata and temporary files are excluded through `.gitignore`.
