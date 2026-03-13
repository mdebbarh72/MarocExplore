# MarocExplore Project Queries

This document contains a comprehensive list of all database queries used in the MarocExplore project, including Requested Query Builder snippets and existing queries from the application controllers.

## 1. Requested Query Builder Snippets

### Get all itineraries with their destinations
```php
$itineraries = Itenerary::with('destinations')->get();
```

### Search and Filtering
Search for itineraries containing a keyword in the title and filter by category.
```php
$query = Itenerary::query()->with('destinations');

// Search by title
if ($request->has('search')) {
    $query->where('title', 'ilike', '%' . $request->search . '%');
}

// Filter by category
if ($request->has('category')) {
    $query->where('category_id', $request->category);
}

// Search by destination title
if ($request->has('destination')) {
    $query->whereHas('destinations', function ($q) use ($request) {
        $q->where('title', 'ilike', '%' . $request->destination . '%');
    });
}
```

### Most Popular Itineraries (By Title)
Retrieve itineraries that appear most frequently by title (indicating a high number of copies).
```php
$popularItineraries = Itenerary::select('title', DB::raw('count(*) as total_count'))
    ->groupBy('title')
    ->orderBy('total_count', 'desc')
    ->get();
```

### Statistics: Total number of itineraries per category
```php
$statsByCategory = Itenerary::select('category_id', DB::raw('count(*) as total'))
    ->groupBy('category_id')
    ->with('category')
    ->get();
```

### Statistics: Total number of registered users per month
```php
$usersByMonth = User::select(
        DB::raw("EXTRACT(YEAR FROM created_at) as year"),
        DB::raw("EXTRACT(MONTH FROM created_at) as month"),
        DB::raw("COUNT(*) as total")
    )
    ->groupBy('year', 'month')
    ->orderBy('year', 'desc')
    ->orderBy('month', 'desc')
    ->get();
```

---

## 2. Existing Controller Queries

### AuthController
- **Login**: Find user by email.
  ```php
  User::where('email', $request->email)->first();
  ```
- **Sign Up**: Create user.
  ```php
  User::create([
      'name' => $request->name,
      'email' => $request->email,
      'password' => Hash::make($request->password),
  ]);
  ```

### IteneraryController
- **Index**: List with destinations, search, and category filter (Paginated).
  ```php
  Itenerary::query()->with('destinations')->paginate(10);
  ```
- **Show**: Get specific itinerary with relations.
  ```php
  $itinerary->load('destinations.places', 'destinations.activities', 'destinations.dishes');
  ```
- **User Itineraries**: Get authenticated user's itineraries.
  ```php
  $request->user()->iteneraries()->with('destinations')->paginate(10);
  ```
- **Copy**: Replicate an itinerary and its children.
  ```php
  $clone = $itinerary->replicate();
  // ... clone nested destinations, activities, places, and dishes via relations
  ```
- **Update**: Update itinerary and nested relations.
  ```php
  $itinerary->update([...]);
  // Activities, Places, Dishes, Destinations: delete/create in transaction
  ```

### DestinationController
- **Store/Update**: Create/Update destination and its nested children (Activities, Places, Dishes).
  ```php
  $itinerary->destinations()->create([...]);
  $destination->update([...]);
  // Child nested models are handled via create/delete loops.
  ```
