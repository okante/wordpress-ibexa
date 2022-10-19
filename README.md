# almaviacx/wordpress-ibexa

This bundle import data from Wordpress into ibexa. this is a Beta-version
Content imported from Wordpress
 - Catagory
 - post and page as child of category if they are related to category (multiple location of content is coming soon)
 - image and author as related object to post and page

Notice that Author are imported as content and not User

## Requirements:

As this Bundle is now based on [WP API ](https://developer.wordpress.org/rest-api/reference/) it should be enabled on the wordpress website to make the bundle Work

## Installation:
### Configure Packge Repository:

### Require the bundle

```
composer require "almaviacx/wordpress-ibexa:dev-main"
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
      url: 'https://myblog.example.com'
      posts:
        parent_location: 2
        per_page: 15
        content_type: 'post'
        slug_field: 'slug'
        mapping:
          title: 'title'
          link: 'link'
          body: 'content'
          date: 'date'
          modified: 'modified'
          slug: 'slug'
          author: 'authorContentInfo' ## special case for author. TODO manage relations
          media: 'imageContentInfo' ## special case for featured image. TODO manage relations

      pages:
        parent_location: 2
        per_page: 15
        content_type: 'post'
        slug_field: 'slug'
        mapping:
          title: 'title'
          link: 'link'
          body: 'content'
          date: 'date'
          modified: 'modified'
          slug: 'slug'
          author: 'authorContentInfo'
          media: 'imageContentInfo'

      categories:
        parent_location: '%env(int:POST_PARENT_LOCATION)%'
        per_page: 15
        content_type: 'category'
        slug_field: 'identifier'
        mapping:
          title: 'name'
          link: 'link'
          identifier: 'slug'
          description: 'description'
      users:
        parent_location: 2
        per_page: 15
        content_type: author
        slug_field: 'identifier'
        mapping:
          title: 'name'
          link: 'link'
          url: 'url'
          identifier: 'slug'
          description: 'description'
      tags:
        parent_location: 3
        per_page: 15
        content_type: author
        slug_field: 'identifier'
        mapping:
          name: 'name'
          link: 'link'
          url: 'url'
          identifier: 'slug'
          description: 'description'

      image:
        parent_location: 43
        per_page: 15
        content_type: 'image'
        mapping:
          name: 'title'
          image: 'source_url'
```

### import posts

```
php bin/console wordpress:ibexa:import:category-tree --siteaccess=site
php bin/console wordpress:ibexa:import:post --siteaccess=site
php bin/console wordpress:ibexa:import:page --siteaccess=site
      
```
