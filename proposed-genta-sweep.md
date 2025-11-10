Summary: proposed sweep for literal '/GENTA'

Goal
- Replace unsafe hardcoded '/GENTA' occurrences with helper-based URL generation or environment-driven values where safe.

Findings
- The repo contains many textual mentions of '/GENTA' but most are logs, comments, or required config (e.g., `.htaccess`, `config/app_local.php`). These should not be changed automatically.
- Client-side code does not contain unsafe string concatenations that hardcode '/GENTA' except for one smoke test which contained a fallback literal.

Files of interest (checked)
- config/app_local.php — contains `'base' => '/GENTA'` (intentional; do not change)
- webroot/.htaccess and .htaccess — have RewriteBase /GENTA/ (required for subfolder deployment)
- templates/layout/guest-layout.php and templates/layout/teacher-layout.php — reference assets with leading-slash paths like `/assets/...` (acceptable; Cake helpers will produce correct URLs when App.base is set)
- webroot/assets/js/misc.js — comments reference '/GENTA' but no code literal that requires change
- tests/smoke/idle-expiry-smoke.js — contained a literal fallback `'/GENTA'` (changed to `'/'` to prefer env var)
- logs/* — many occurrences; do not edit

Actions taken (safe, applied)
- Updated `tests/smoke/idle-expiry-smoke.js` to remove the hardcoded '/GENTA' fallback and default to '/'. This keeps the test flexible and avoids hardcoded app-subpaths.

Proposed (conservative) patch suggestions (for review)
1) No further automatic changes recommended. The repository is already using CakePHP helpers and `window.APP_BASE` consistently. A broad replace would risk touching logs and configuration.

2) Optional small polish (manual review before apply):
   - Replace leading-slash asset paths in layouts with helper calls where helpful, for clarity. Example (one-line replacement suggestion):

     In `templates/layout/guest-layout.php` replace:
       <?= $this->Html->image('/assets/images/genta-logo1.png', ['alt' => 'GENTA Icon']) ?>
     with:
       <?= $this->Html->image('genta-logo1.png', ['pathPrefix' => '/assets/images/', 'alt' => 'GENTA Icon']) ?>

     Note: This is stylistic. The current code works with App.base set. Make this change only if you want uniform helper usage.

3) Add an explicit developer note in `README.md` or `config/README` reminding contributors to use `window.APP_BASE` or Cake's Url/Html helpers when building client JS or templates. This reduces future regressions.

How to proceed
- If you want I can:
  A) Leave everything as-is (recommended; already fixed the one test literal).
  B) Apply the optional small polish (prepare patches for layout files to replace asset strings with explicit helper usage). I'll produce diffs for review first.
  C) Run a more targeted search-and-replace (only inside `webroot/assets/js` and `templates`) and prepare detailed diffs for each change for your review.

Tell me which option (A/B/C) you prefer and I'll prepare the diffs for review or apply them after your approval.
