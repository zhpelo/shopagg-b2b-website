<?php
declare(strict_types=1);

namespace App\Plugins\Http;

final class Request {
    private function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $form,
        public readonly array $files,
        public readonly array $headers,
        public readonly string $rawBody
    ) {}

    public static function capture(): self {
        $headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];
        if ($headers === []) {
            foreach ($_SERVER as $key => $value) {
                if (str_starts_with($key, 'HTTP_')) $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))))] = (string)$value;
            }
            if (isset($_SERVER['CONTENT_TYPE'])) $headers['Content-Type'] = (string)$_SERVER['CONTENT_TYPE'];
            if (isset($_SERVER['CONTENT_LENGTH'])) $headers['Content-Length'] = (string)$_SERVER['CONTENT_LENGTH'];
        }
        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            (string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'),
            $_GET,
            $_POST,
            $_FILES,
            array_change_key_case($headers, CASE_LOWER),
            (string)file_get_contents('php://input')
        );
    }

    public function input(string $key, mixed $default = null): mixed { return $this->form[$key] ?? $this->query[$key] ?? $default; }
    public function header(string $name, ?string $default = null): ?string { return $this->headers[strtolower($name)] ?? $default; }
    public function json(): array { $data = json_decode($this->rawBody, true); return is_array($data) ? $data : []; }
}
