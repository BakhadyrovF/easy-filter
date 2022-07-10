# Easy Query Filter
### **The package is written for experience.**
**This is a package that filter queries with user's custom methods.**

# Dependencies
- PHP >= 8.0
- Laravel >= 9.0

# Installation
```
composer require bakhadyrovf/easy-filter
```
Laravel uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

# Usage
First of all, you must create a filter class:
```
php artisan make:filter ArticleFilter
```
This command creates **ArticleFilter** class in your project's **app/Filters** folder

Also, you can generate a filter class with folder:
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

Now, you can write your methods inside filter class.
Let's add first method and try filter our query.
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

And you can try filter using **filter** method, method takes a **model class** that must be filtered or **Illuminate\Database\Eloquent\Builder**.
Method return's **Illuminate\Database\Eloquent\Builder**, so you can continue your query building;
In **app\Http\Controllers\ArticleController**:
```php
use App\Filters\ArticleFilter;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $articles = ArticleFilter::filter(User::class)
            ->orderByDesc('id')
            ->get();
            
        return $articles;
    }
}
```   

All parameters that are responsible for filtering must be in the **filters** array:
```
example.com/articles?filters[title]=some-title
```
> If your parameter is in snake case, you don't need to create a method with the same case,
because it doesn't match [php standards](https://www.php-fig.org/psr/psr-12/#44-methods-and-functions).
The package itself converts the parameter to camel case.

For example if your parameter is **first_name**:
```
example.com/articles?filters[first_name]=some-value
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


If your parameter can take multiple values, you can use array:
```
example.com/articles?filters[category_ids]=[1,2,3,4,5]
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
