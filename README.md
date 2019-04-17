# liam-wiltshire/laravel-model-meta

liam-wiltshire/laravel-model-meta is an extension to the default Laravel Eloquent model to add metadata to a model.

# What MetaData?
While Eloquant is very good at handling relational data, however not all data we deal with works like this. Without going as far as using a NoSQL solution such as Mongo, using mySQL to hold relational data, but with a JSON meta field is a potential solution.

liam-wiltshire/laravel-model-meta allows you to use standard Eloquent getters and setters to add and remove metadata to your models. Any attributes that relate to a column in your database table will be handled as a standard attribute, but anything else will be added to the meta data.

# Example

```php
$test = new \App\Models\Test();

$test->forceFill(
[
    'subject_id' => 1,
    'level_id' => 1,
    'slug' => 'old-slug',
    'title' => 'Test Title',
    'instructions' => 'Some instructions'
]
);

$test->save();

$test = \App\Models\Test::find(1);

//This is an actual field in the DB, so this will be set to that attribute
$test->slug = 'test-slug';

//This isn't a field in the DB, and isn't a relationship etc, so will be stored in the meta field
$test->meta_subject = 'This is a meta subjects';

$test->save();
```
This code would generate a new `Test` model and save it to the DB. Assuming that `meta_subject` is not a column in our table, the `meta_subject` will automatically be added to the metadata:

```text
root@localhost:[homestead]> SELECT id, slug, title, meta FROM tests;
+----+-----------+------------+--------------------------------------------+
| id | slug      | title      | meta                                       |
+----+-----------+------------+--------------------------------------------+
|  1 | test-slug | Test Title | {"meta_subject":"This is a meta subjects"} |
+----+-----------+------------+--------------------------------------------+
```

# Installation
liam-wiltshire/laravel-model-meta is available as a composer package:
`composer require liam-wiltshire/laravel-model-meta`

Once installed, use the `\LiamWiltshire\LaravelModelMeta\Concerns\HasMeta` trait in your model.

The database table behind the model will need a meta column adding - by default the trait assumes this will be called `meta`:

```php
$table->json('meta')->nullable()->default(null);
```

If the name of your meta column is different, then add a `$metaDbField` property to your model containing the name of the field:

```php
protected $metaDbField = 'metaData';
```

# Limitations
This is a pre-release. Many cases have not been completely tested yet, and unexpected results may be returned. You have been warned!
