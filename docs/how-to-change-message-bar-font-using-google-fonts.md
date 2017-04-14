# How to Change Message Bar Font Using Google Fonts?

GEO Top Bar provides you with easy access to any fonts you want from Google Fonts. Choose between more than 600 fonts.

?> This is all done with a live preview that allows you to immediately see what effect your changes will have on your GEO Top Bar content. However, visitors to your website won’t see those changes until you hit the **Save & Publish** button.

Navigate to **Appearance > Customize > GEO Top Bar > Typography** and here you will be able to locate the **"Font Family"** option.

![Google Font Family](http://res.cloudinary.com/mypreview/image/upload/v1492202359/change-google-web-fonts_smqck5.gif)

### Property Values

* ```Default```:  The **default** value. With selecting this, the plugin will inherit default font family of your theme.
* ```Any Google Web Fonts```: Select from a list of Google Fonts, the best free fonts available.

## Load all available Google fonts

By default only a limited number (30) of fonts are available with the font family drop-down list. While all fonts collected and selected carefully from the most popular and legible fonts, the plugin allows you to directly communicate with the Google Web Fonts Directory to select from any of the available fonts and stay up to date with the list of published fonts at any time.

!> You can place PHP snippets at the bottom of your child theme ```functions.php``` file.

```php
if (!function_exists('prefix_load_all_geo_top_bar_google_fonts')):
    function prefix_load_all_geo_top_bar_google_fonts()
    {
        return 'all';
    }
endif;
add_filter('mypreview_geo_top_bar_google_fonts_limit', 'prefix_load_all_geo_top_bar_google_fonts', 10);
```
that’s easy right?, In case you want to return a slice of available Google fonts, use this snippet inside of your child theme to return any number of Google fonts that you may need.

## Load slice of available Google fonts

```php
if (!function_exists('prefix_load_slice_of_geo_top_bar_google_fonts')):
    function prefix_load_slice_of_geo_top_bar_google_fonts()
    {
        return 50;
    }
endif;
add_filter('mypreview_geo_top_bar_google_fonts_limit', 'prefix_load_slice_of_geo_top_bar_google_fonts', 10);
```
## Setup Your Google API Key

API keys are unique passphrases that allow websites on your domain to connect to an application interface to make use of its features. Not all APIs require keys, but some do to handle authentication or to keep their platforms safe from being compromised. You can setup your own Google API Key for use with any Google application, including Google Web Fonts.

!> Note that this step is entirely optional, but you may or may not need to setup your own Google API key..

?> Follow these steps to get an API Browser Key from the [Google API Console](https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key)

```php
if (!function_exists('prefix_custom_geo_top_bar_google_fonts_api')):
    function prefix_custom_geo_top_bar_google_fonts_api()
    {
        return 'your-api-key-here';
    }
endif;
add_filter('mypreview_geo_top_bar_google_fonts_api', 'prefix_custom_geo_top_bar_google_fonts_api', 10);
```
