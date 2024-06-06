# Dynamic meta images

Dynamic meta images is a Craft CMS plugin that lets you generate dynamic meta images from your website's content.

## Requirements

This plugin requires Craft CMS 4.0 or 5.0 or later, and PHP 8.0 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Dynamic meta images”. Then press “Install”.

### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/project

# tell Composer to load the plugin
composer require wayborne/dynamic-meta-images

# tell Craft to install the plugin
./craft plugin/install dynamic-meta-images
```

### Additional steps

#### Puppeteers 
This plugin requires you to install Puppeteers

`npm i puppeteer`

#### Node and NPM binary
Create the following enviroment variables in your `.env` file to point at the Node and NPM binary

```bash
NODE_BINARY="/usr/bin/node"
NPM_BINARY="/usr/bin/npm"
```

#### Template folder
When enabling the plugin a new folder is created in your template root folder with the name `_dynamic-meta-images`.
Inside of it will also be a demo template `demo.twig` showcasing some techniques.

## Usage
Dynamic meta images are being created from a twig/html template every time an entry gets saved. The template is rendered in a headless browser and an image is created and saved to a Craft asset sources.


### Options
- Enable/disable the image generation per section and per site 
- Pick a template per section

### File naming
By default the entry id will be used as file name. You can customize (per template) this by passing a `title` tag inside your template:
```
<title>{{ entry.title }}</title>
```

creates a new file:@
`title-of-the-entry.png`

### Stytling the template
You can style your templates however you want, however it's important that all of your styling resources (css/fonts/...) have a public url.
That means that for local development it's easier to use some an existing CDN:

#### TailwindCSS
Include the following script to your header:
```html
<script src="https://cdn.tailwindcss.com"></script>
```
You can even pass it your local theme to overwrite your defeault:


#### Fonts
Any public CDN, for example google fonts:
```
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poetsen+One&display=swap" rel="stylesheet">
<style>
.poetsen {
    font-family: "Poetsen One", sans-serif;
    font-weight: 400;
    font-style: normal;
}
</style>
```

### Together with existing SEO plugins:

#### SEOmatic
Using SEOmatic's existing api you can set the meta images:
```twig
{#-- Get the title --#}
{% set image_name  = entry.id %}
{#-- Check if the asset exists --#}
{% set dynamic_meta_image =  craft.assets().fileName(image_name).one() ?? null  %}
{#-- Test for a public url --#}
{% if  dynamic_meta_image.url %}
    {#-- Set the meta image --#}
    {% do seomatic.meta.seoImage(dynamic_meta_image.url) %}
{% else %}
... fallback
{% endif %}
```

[Source](https://nystudio107.com/docs/seomatic/advanced.html#variables)

#### SEO fields
Using SEO fields you can manually set the Facebook and Twitter image:
```twig
{#-- Get the title --#}
{% set image_name  = craft.slugify(entry.title) %}
{#-- Check if the asset exists --#}
{% set dynamic_meta_image =  craft.assets.title(image_name).one() ?? null  %}
{% if  dynamic_meta_image %}
    {#-- Set the meta image --#}
    {% do entry.setFacebookImage(dynamic_meta_image) %}
    {% do entry.setTwitterImage(dynamic_meta_image) %}
{% else %}
... fallback
{% endif %}
```
[Source](https://studioespresso.github.io/craft-seo-fields/templating.html)


## Common issues
- I can't find the path to my Node or NPM binary
- Images aren't being generated: 
-- Make sure puppeteers is installed
-- Make sure the path to the Node binary & NPM binarycheck is correct
-- Check the queue logs for more info


Brought to you by [Wayborne](https://wayborne.com)
