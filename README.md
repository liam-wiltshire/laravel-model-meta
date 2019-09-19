# liam-wiltshire/laravel-model-meta

liam-wiltshire/laravel-model-meta is an extension to the default Laravel Eloquent model to add metadata to a model.

# What MetaData?
While Eloquent is very good at handling relational data, however not all data we deal with works like this. Without going as far as using a NoSQL solution such as Mongo, using mySQL to hold relational data, but with a JSON meta field is a potential solution.

liam-wiltshire/laravel-model-meta allows you to use standard Eloquent getters and setters to add and remove metadata to your models. Any attributes that relate to a column in your database table will be handled as a standard attribute, but anything else will be added to the meta data.

This gives you great flexibility to have structured data where appropriate (for example an book will always have a title, author etc), but to then have other related, unstructured data (for example some books might have the number of pages, others might not)

When you are structuring your data, it's important to consider if you want to be able to query this data. Unless you use something like virtual columns at the DB level, this data isn't queryable - it's not designed to store the primary data for that record.

# Example

```php
$book = new \App\Models\Book();

$book->forceFill(
[
    'title' => 'Laravel Quickstart Guide',
    'author_id' => 90,
    'publisher_id' => 5,
    'description' => 'This is an awesome book'
]
);

$book->save();

$book = \App\Models\Book::find(1);

//This is an actual field in the DB, so this will be set to that attribute
$book->title = 'Laravel Quickstart Guide, Second Edition';

//This isn't a field in the DB, and isn't a relationship etc, so will be stored in the meta field
$book->page_count = 200;

$test->save();
```
This code would generate a new `Test` model and save it to the DB. Assuming that `meta_subject` is not a column in our table, the `meta_subject` will automatically be added to the metadata:

```text
root@localhost:[homestead]> SELECT id, title, meta FROM books;
+----+------------------------------------------+--------------------+
| id | title                                    | meta               |
+----+------------------------------------------+--------------------+
|  1 | Laravel Quickstart Guide, Second Edition | {"page_count":200} |
+----+------------------------------------------+--------------------+
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
