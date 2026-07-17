# Middleware Pipeline

A lightweight and framework-independent middleware pipeline for PHP.

This package allows you to execute one or more middleware classes before and after calling a class method. It follows the same pipeline pattern used by Laravel's middleware system but can be used in any PHP project.

## Features

- 🚀 Simple and lightweight
- 📦 No framework dependency
- 🔗 Supports multiple middleware
- 🔄 Execute code before and after the target method
- 🧩 Supports both object and static method calls
- ⚡ PHP 8.0+

---

## Requirements

- PHP 8.0 or higher

---

## Installation

Install via Composer:

```bash
composer require your-vendor/middleware-pipeline
```

---

## Middleware Structure

Every middleware must implement a `handle()` method.

Example:

```php
class LoggerMiddleware
{
    public function handle($request, $next)
    {
        // Before

        $result = $next($request);

        // After

        return $result;
    }
}
```

The `$next` callback continues the execution of the pipeline.

---

## Using the Trait

Add the trait to your class.

```php
use ASB\Middleware\MiddlewarePipeline;

class UserService
{
    use MiddlewarePipeline;

    public function save($name)
    {
        return "User {$name} saved.";
    }
}
```

---

## Object Example

```php
$service = new UserService();

$result = $service
    ->middleware([
        LoggerMiddleware::class,
        AuthMiddleware::class,
    ])
    ->save("John");
```

Execution order:

```
LoggerMiddleware
    ↓
AuthMiddleware
    ↓
save()
    ↑
AuthMiddleware
    ↑
LoggerMiddleware
```

---

## Static Example

```php
class UserService
{
    use MiddlewarePipeline;

    public static function save($name)
    {
        return "User {$name} saved.";
    }
}

$result = UserService::middlewared([
    LoggerMiddleware::class,
])->save("John");
```

---

## How It Works

The package builds a middleware pipeline using nested closures.

Calling

```php
$pipeline($params);
```

starts the execution.

Execution flow:

```
Request
    │
    ▼
Middleware A
    │
    ▼
Middleware B
    │
    ▼
Middleware C
    │
    ▼
Target Method
    │
    ▲
Middleware C
    ▲
Middleware B
    ▲
Middleware A
    │
    ▼
Response
```

Each middleware can:

- Execute code before the target method.
- Execute code after the target method.
- Modify the returned value.
- Stop the pipeline without calling `$next`.

---

## Example Middleware

```php
class LoggerMiddleware
{
    public function handle($request, $next)
    {
        echo "Before\n";

        $result = $next($request);

        echo "After\n";

        return $result;
    }
}
```

---

## Multiple Middlewares

```php
$service
    ->middleware([
        AuthMiddleware::class,
        ValidationMiddleware::class,
        LoggerMiddleware::class,
    ])
    ->save();
```

Execution order:

```
Auth
    ↓
Validation
    ↓
Logger
    ↓
save()
    ↑
Logger
    ↑
Validation
    ↑
Auth
```

---

## License

This project is open-sourced software licensed under the MIT License.

---

## Author

Developed by **Abouzar Rostami**.