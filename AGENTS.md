# Agent instructions

<!-- pollora-harness:start -->
## Pollora harness

This repository follows the Pollora harness (profile `core`, observe mode). The same gates run on your machine, in agent hooks and in CI; a pull request merges only when they pass.

### Before you hand back

- `composer qa:fast` runs the fast gates on what you changed (style, static analysis, architecture, dependencies). It must pass.
- `composer qa` runs every gate, as CI does.
- `vendor/bin/pollora-qa rules <ID>` explains a rule quoted in a failure.

### Never

- Never edit `.harness.json`, `phpstan-baseline.neon`, `api-surface.json`: they hold the thresholds. If one is wrong, stop and explain why to the user.
- Never add `@phpstan-ignore`, a baseline entry or a `nosemgrep` comment without a written reason on the same line.
- Never weaken a test to make it pass, and never mark a test skipped to get a green run.
- Never break the public API (attributes, hooks fired, configuration keys, commands) in a minor version: deprecate first.

### Always

- A bug fix comes with a test that fails without the fix.
- Domain code depends on ports (interfaces), never on WordPress functions, Laravel or the Infrastructure layer.
- The common case of a public API needs no configuration; options are chained methods or named arguments.

### Rules checked

- **ARCH-01** Domain code imports neither Illuminate nor the Infrastructure or UI layers _(Pest arch, G1)_
- **ARCH-02** Domain code calls no global WordPress function _(PHPStan rule pollora.noWordPressCallInDomain, G1)_
- **ARCH-03** Application code depends on the Domain layer only _(Pest arch, G1)_
- **ARCH-04** A core package requires neither illuminate/* nor laravel/*, unless listed in .harness.json _(pollora-qa deps, G1)_
- **ARCH-05** The framework core references no third-party plugin (WooCommerce, ACF, Meta Box, WP Rocket) _(Semgrep, G1)_
- **ARCH-06** Only integration packages, templates and projects depend on pollora/framework _(pollora-qa deps, G1)_
- **ARCH-07** Every dependency used is declared, none declared is unused _(composer-dependency-analyser, G1)_
- **ARCH-08** Attribute classes are final, readonly and declare their targets _(Pest arch, G1)_
- **ARCH-09** declare(strict_types=1) in every file _(Pest arch + Pint, G1)_
- **CONV-03** Laravel naming: ServiceProvider, Command, Exception, Repository, Factory suffixes _(Pest arch, G1)_
- **CONV-04** Hooks fired by Pollora are named pollora/<module>/<event_snake_case> _(pollora-qa surface, G2)_
- **CONV-05** Configuration lives under pollora.<package>; env() is only called in config/ _(Pest arch, G1)_
- **CONV-06** Commands are named pollora:<verb> or pollora:make-<thing> _(pollora-qa surface, G2)_
- **CONV-07** Domain and Application code never calls app() or resolve(); it receives contracts _(Pest arch, G1)_
- **CONV-10** One named exception per business case; never throw a bare \Exception _(Semgrep, G1)_
- **CONV-11** Native types everywhere; PHPDoc only for generics, array shapes, @internal, @deprecated _(PHPStan + Pest type coverage, G1)_
- **SEC-01** A REST route declared by attribute states its permission; a public route says so explicitly _(Semgrep, G1)_
- **SEC-03** No $wpdb query built by interpolation without prepare() _(Semgrep, G1)_
- **SEC-04** No unescaped Blade output {!! !!} outside a commented allowlist _(Semgrep, G1)_
- **SEC-05** No unserialize, eval or extract _(Semgrep, G1)_
- **SEC-08** No secret in the repository _(gitleaks, G1)_
- **SEC-09** No dependency with a known vulnerability _(composer audit, G1)_
- **QA-01** Ratchet: no metric gets worse (PHPStan level and baseline, coverage, mutation score) _(pollora-qa ratchet, G2)_
- **QA-02** The harness files are protected: baseline, .harness.json and api-surface.json change only through a human _(Agent hooks + CODEOWNERS, G0)_
- **QA-04** The public surface (attributes, hooks fired, configuration keys, commands) changes only with a label: api-change to add, breaking to remove or change _(pollora-qa surface, G2)_
- **QA-05** No PHP signature breaks backward compatibility outside a major version _(roave/backward-compatibility-check, G2)_
- **QA-03** A change to a core package keeps the framework green _(Downstream job, G2)_
<!-- pollora-harness:end -->
