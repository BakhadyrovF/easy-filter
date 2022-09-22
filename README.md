# Easy Query Filter
**This is a package that filter queries with user's custom methods.**

# Dependencies
- PHP >= 8.0
- Laravel >= 9.0

# Installation
```
composer require bakhadyrovf/easy-filter
```
Laravel uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

#### Copy the package config to your local config with the publish command.
```
php artisan vendor:publish --tag=easy-filter-config
```

# Usage
### Important:
The namespace for filters by default `App\Filters` and each filter class before creating expects that you already have **Eloquent Model** with the namespace standards as follows:
|Filter|Model|
|-----|-----------|
|`App\Filters\UserFilter`|`App\Models\User`|
|`App\Filters\Dashboard\ArticleFilter`|`App\Models\Dashboard\Article`|
|`App\EloquentFilters\TagFilter`|`App\Models\Tag`|


First of all you must create filter class:
```
php artisan make:filter ArticleFilter
```
This command creates **ArticleFilter** class in your project's **app/Filters** folder.
(*Also this command adds trait **Filterable** to your **Eloquent Model***)

You can change base folder's name in your config file *(**Namespace** will be changed as well)*:
```php
<?php

return [

    /**
     * Base folder's name in app directory.
     * Default: Filters
     */
    'base-folder' => 'Filters'

];
```

Also, you can generate a filter class with subfolder:
```
php artisan make:filter Dashboard/ArticleFilter
```
Filter class will be located in **app/Filters/Dashboard** folder.

The newly created class will looks like this:
```php
<?php 

namespace App\Filters\ArticleFilter;

use Bakhadyrovf\EasyFilter\QueryFilter;

class ArticleFilter extends QueryFilter
{
    
}
```

If the namespace of your **Model** or **Filter** does not match the above, you can use `--model` option:
For example - we have model with namespace `App\Models\Article`,     
we want to create a filter class with namespace `App\Filters\Dashboard\ArticleFilter`:
```
php artisan make:filter Dashboard/ArticleFilter --model=App\Models\Article
```
In order for a filter that does not match the namespace with the model, you must add method on your `App\Models\Article` class:
```php
<?php

namespace App\Models\Admin;

use Bakhadyrovf\EasyFilter\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Filters\Dashboard\ArticleFilter;

class Discount extends Model
{
    use HasFactory, Filterable;
    
    public function provideFilter()
    {
        return ArticleFilter::class;
    }
}
```
This method will point to a specific filter class.

Now, you can write your methods inside filter class.
Let's add first method and try to filter our query.
In **app/Filters/ArticleFilter**:
```php
class ArticleFilter extends QueryFilter
{
    public function title(Builder $builder, $value)
    {
        return $builder->where('title', 'LIKE', $value . '%');           
    }
}
```
**Method Arguments**
- $builder - Illuminate\Database\Eloquent\Builder
- $value - Value taken from request

And you can try to filter query using `filter()` method on your **Eloquent Model**.
This method is basic [Eloquent Scope](https://laravel.com/docs/9.x/eloquent#query-scopes) so you can use it as usually.
In **app\Http\Controllers\ArticleController**:
```php
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $articles = Article::filter()
            ->orderByDesc('id')
            ->get();
            
        return $articles;
    }
}
```   

All parameters that are responsible for filtering must be in query:
```
example.com/articles?title=some-title
```
> If your parameter is in snake case, you don't need to create a method with the same case,
because it doesn't match [php standards](https://www.php-fig.org/psr/psr-12/#44-methods-and-functions).
The package itself converts the parameter to camel case.

For example if your parameter is **first_name**:
```
example.com/articles?first_name=some-value
```
Method will be looks like this:
```php
Class ArticleFilter extends QueryFilter 
{
    public function firstName(Builder $builder, $value)
    {
        return $builder->where('first_name', 'LIKE', $value . '%');
    }
}
```

**Multiple values**
If your parameter can take multiple values, you can use brackets:
```
example.com/articles?category_ids=[1,2,3,4,5]
```
As usual, these values will be in the method's second argument
```php
Class ArticleFilter extends QueryFilter 
{
    public function categoryIds(Builder $builder, $values)
    {
        return $builder->whereHas('categories', function ($query) use ($values) {
            return $query->whereIn('id', $values);
        });
    }
}
```

**Ignoring parameters**
For example if you want to ignore **post_ids** parameter from filtering:
```
https://example.com/users?name=Firuzbek&post_ids=[1,10,25]
```
You can provide exceptions array of **method names** or **query parameters** as an argument to `filter()` method:
```php
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $articles = User::active()
            ->filter(['postIds']) // Or post_ids
            ->orderByDesc('created_at')
            ->get();
            
        return $articles;
    }
}
```

