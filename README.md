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

#### Puppeteer
This plugin requires you to install Puppeteer

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
{% set image_name  = entry.id %}
{#-- Check if the asset exists --#}
{% set dynamic_meta_image =  craft.assets().fileName(image_name).one() ?? null  %}
{#-- Set the meta image --#}
{% if dynamic_meta_image %}
    {% do entry.setFacebookImage(dynamic_meta_image) %}
    {% do entry.setTwitterImage(dynamic_meta_image) %}
{% else %}
    ... fallback
{% endif %}
```
[Source](https://studioespresso.github.io/craft-seo-fields/templating.html)


## Troubleshooting

### I can't find the path to my Node or NPM binary
For Node.js: Type which node (macOS/Linux) or where node (Windows) and press Enter. This will display the path to the Node.js binary.
For npm: Type which npm (macOS/Linux) or where npm (Windows) and press Enter. This will display the path to the npm binary.
### Images aren't being generated: 
All image creation is being done in the queue logs so if you experience any issues, that's a good place to check. Make sure that:
- Puppeteer is installed
- The NODE_BINARY and NPM_BINARY is set


To enable Puppeteer Headless Chrome support, add the following line to your `/.ddev/config.yaml` file:

```yaml
webimage_extra_packages: [ gconf-service, libasound2, libatk1.0-0, libcairo2, libgconf-2-4, libgdk-pixbuf2.0-0, libgtk-3-0, libnspr4, libpango-1.0-0, libpangocairo-1.0-0, libx11-xcb1, libxcomposite1, libxcursor1, libxdamage1, libxfixes3, libxi6, libxrandr2, libxrender1, libxss1, libxtst6, fonts-liberation, libappindicator1, libnss3, xdg-utils ].
```

For Apple Silicon support, you will have to override that configuration by adding a config.m1.yaml file in your ddev folder along with the config.yaml one with the following content:
```yaml
webimage_extra_packages : [chromium]
web_environment:
- CPPFLAGS=-DPNG_ARM_NEON_OPT=0
- PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium
- PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
```

Brought to you by [Wayborne](https://wayborne.com)
