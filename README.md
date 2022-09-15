# almaviacx/wordpress-ibexa


## Installation:
### Configure Packge Repository:

```
### Require the bundle

```
composer require "almaviacx/wordpress-ibexa:^1.0"
```

### Enable the bundle
Add this configuration to the RegisterBundle array section in `Kernel.php`

```php
    Almaviacx\Bundle\Ibexa\WordPress\WordPressIbexaBundle::class => ['all' => true],
```
Configure the Bundle
```yaml
word_press_ibexa:
  system:
    site_group:
      per_page: 10
      url: 'https://blog.hibu.com'
      posts:
        parent_location: 349
        per_page: 10
        content_type: 'test_richtext'
        mapping:
          title: 'title'
          richtext: 'content'

      pages:
        parent_location: 349
        per_page: 10
        content_type: 'test_richtext'
        mapping:
          title: 'title'
          richtext: 'content'

      categories:
        parent_location: 349
        per_page: 10
        content_type: 'test_richtext'
        mapping:
          title: 'title'
          richtext: 'content'

      tags:
        parent_location: 349
        per_page: 10
        content_type: 'test_richtext'
        mapping:
          title: 'title'
          richtext: 'content'
```

### import posts

```
php bin/console wordpress:ibexa:import:category-tree --siteaccess=marqueatlantic_fr
php bin/console wordpress:ibexa:import:post --siteaccess=marqueatlantic_fr
   
      
```
