# Third-Party Licenses

HelpdeskAI itself is **commercial proprietary software** (see [LICENSE](LICENSE)).
That proprietary license applies to HelpdeskAI's own code — **not** to the
third-party packages listed below, which remain governed by their respective
licenses. Dependency LICENSE files are not removed or altered.

Generated from `composer licenses` + `npm list` (October 2026). Regenerate with:

```bash
composer licenses
npm list --depth=0
```

## PHP (Composer)

| Package | Version | License |
|---|---|---|
| barryvdh/laravel-dompdf | v3.1.2 | MIT |
| brick/math | 0.14.8 | MIT |
| doctrine/dbal | 4.4.3 | MIT |
| dompdf/* | v3.1.5 / 1.0.2 | LGPL-2.1 / LGPL-3.0-or-later |
| dragonmantank/cron-expression | v3.6.0 | MIT |
| fakerphp/faker | v1.24.1 | MIT |
| guzzlehttp/* | 7.10.0 | MIT |
| laravel/* (framework, breeze, sanctum, reverb, pint, …) | v13.7.0 | MIT |
| league/* (commonmark, config, flysystem, uri) | 2.8.2 / 1.2.0 / 3.33.0 / 7.8.1 | BSD-3-Clause / MIT |
| mockery/mockery | 1.6.12 | BSD-3-Clause |
| monolog/monolog | 3.10.0 | MIT |
| nesbot/carbon | 3.11.4 | MIT |
| nette/* | v1.3.5 / v4.1.3 | BSD-3-Clause, GPL-2.0-only, GPL-3.0-only |
| phpoffice/phpspreadsheet (+ markbaker/*) | v5.10.0 | LGPL-2.1 (library) |
| spatie/laravel-permission | 7.4.1 | MIT |
| symfony/* | v7.4.x | MIT |
| vlucas/phpdotenv | v5.6.3 | BSD-3-Clause |
| hamcrest, phpunit, sebastian/*, myclabs, theseer, tijsverkoyen, filp/whoops, masterminds, maennchen, league/mime-type-detection, voku, dflydev, clue/*, evenement, egulias, graham-campbell, psr/*, rahul900, staabm, thecodingmachine | various | MIT / BSD-3-Clause (dev & transitive) |

Full authoritative list: run `composer licenses` in this repository.

## JavaScript (npm, bundled locally via Vite — no CDN)

| Package | Version | License |
|---|---|---|
| @tabler/core | 1.6.1 | MIT |
| @fontsource/inter | 5.3.0 | OFL-1.1 (font) / MIT (packaging) |
| alpinejs | 3.15.12 | MIT |
| apexcharts | 7.8.0 | MIT |
| axios | 1.16.1 | MIT |
| laravel-echo | 2.5.0 | MIT |
| pusher-js | 8.6.0 | MIT |
| vite, laravel-vite-plugin, concurrently | 8.0.10 / 3.1.0 / 9.2.1 | MIT (build-time) |

## Notes

- Copyleft-licensed code (LGPL/GPL components above) is used as **unmodified libraries**; their licenses do not transfer to HelpdeskAI's own code.
- No dependency is re-licensed as proprietary by this project.
