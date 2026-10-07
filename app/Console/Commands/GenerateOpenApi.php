<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class GenerateOpenApi extends Command
{
    protected $signature = 'openapi:generate {--output= : Output file (default: public/openapi.json)}';

    protected $description = 'Generate OpenAPI spec from registered API routes (never hand-written, always in sync)';

    public function handle(): int
    {
        $paths = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            if (! str_starts_with($uri, 'api/')) {
                continue;
            }

            $openApiPath = '/'.preg_replace('/\{(\w+)\?\}/', '{$1}', $uri);
            $openApiPath = preg_replace_callback(
                '/\{(\w+)\}/',
                fn ($m) => '{'.$m[1].'}',
                $openApiPath
            );

            foreach (array_diff($route->methods(), ['HEAD', 'OPTIONS']) as $method) {
                $paths[$openApiPath][strtolower($method)] = [
                    'operationId' => $route->getName() ?: strtolower($method).'_'.str_replace(['/', '{', '}'], ['_', '', ''], $uri),
                    'summary' => $this->summary($route),
                    'security' => [['bearerAuth' => []]],
                    'tags' => [$this->tag($uri)],
                    'parameters' => $this->pathParameters($route),
                    'responses' => [
                        '200' => ['description' => 'OK — envelope {success, data, message}'],
                        '201' => ['description' => 'Created'],
                        '401' => ['description' => 'Unauthenticated'],
                        '403' => ['description' => 'Forbidden'],
                        '422' => ['description' => 'Validation error'],
                    ],
                ];
            }
        }

        ksort($paths);

        $spec = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => config('app.name', 'HelpDesk AI').' API',
                'version' => trim((string) (is_file(base_path('VERSION')) ? file_get_contents(base_path('VERSION')) : 'dev')),
                'description' => 'Auto-generated from routes/api.php. Auth: Sanctum Bearer token or scoped API key (X-API-Key). Envelope: {success, data, message}.',
            ],
            'servers' => [['url' => rtrim(config('app.url'), '/').'/api']],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => ['type' => 'http', 'scheme' => 'bearer', 'bearerFormat' => 'Sanctum|API-Key'],
                ],
            ],
            'paths' => $paths,
        ];

        $output = $this->option('output') ?: public_path('openapi.json');
        file_put_contents($output, json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        $this->info('OpenAPI spec written to '.$output.' ('.count($paths).' paths).');

        return self::SUCCESS;
    }

    protected function summary($route): string
    {
        $action = $route->getActionName();

        if (str_contains($action, '@')) {
            [$controller, $method] = explode('@', class_basename($action));

            return $controller.'@'.$method;
        }

        return $route->getName() ?? $route->uri();
    }

    protected function tag(string $uri): string
    {
        $segments = explode('/', trim($uri, '/'));

        return ucfirst($segments[1] ?? 'default');
    }

    protected function pathParameters($route): array
    {
        $params = [];

        foreach ($route->parameterNames() as $name) {
            $params[] = [
                'name' => $name,
                'in' => 'path',
                'required' => true,
                'schema' => ['type' => 'string'],
            ];
        }

        return $params;
    }
}
