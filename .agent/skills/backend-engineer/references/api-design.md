# API Design (Laravel)

## 1. RESTful Standards
```php
// Route Naming (Resources)
Route::apiResource('posts', PostController::class); // index, store, show, update, destroy
```

## 2. API Resources (Transformation)
```php
class PostResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'owner' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
```

## 3. Validation & Response
```php
// Request: Use FormRequest for rules() and authorize()
$post = Post::create($request->validated());

// Response: Consistent JSON structures
return new PostResource($post); // Status 201 via created() or 200 default
```

## 4. Documentation (Scribe)
```bash
# Automated API Docs
php artisan scribe:generate
```

## 5. Versioning
- **URL Based:** `api/v1/...` (Default for clarity).
- **Header Based:** `Accept: application/vnd.app.v1+json`.
- **Lifecycle:** Mark old endpoints as `@deprecated` in docs.