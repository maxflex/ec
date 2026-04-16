## Common info

This is a Laravel project used as backend-only API.
CRM and MOB – are Nuxt 3 frontends using this API.

## Structure

– `app/Console/Commands/Transfer` no need to analyze the code there
because it was only used once in the beginning when we were transferring
data from our previous version of the app

– `app/Console/Commands/Once` those are commands that were used just once
for some specific purpose and will be deleted any time

## Testing

Never run test suites as there's no testing code in this project

## Code style

- Avoid "noise" checks in PHP:
  - do not add explicit casts like `(string)` / `(int)` without a real need;
  - do not add `trim()` and similar normalizations "just in case";
  - rely on Laravel behavior where empty strings are typically converted to `NULL`, and prefer simple checks like `if (! $model->field)`.

## Common info

We have 3 roles in app: admin, client and teacher. Each role has it's
routes and controllers folder. `routes/pub.php` are the routes available
without auth. For example, it is used for making requests from our
public website (fetch reviews, handle client request etc.)
