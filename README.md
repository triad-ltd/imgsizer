The ImageSizer plugin will resize any JPG, GIF or PNG image to the size specified
and cache the resized image to the cache folder.

It can also output a placeholder image appropriately scaled which is ideal for lazy-loading situation.


# History

Originally from [http://devot-ee.com/add-ons/image-sizer](http://devot-ee.com/add-ons/image-sizer) and then forked from [https://github.com/ctmaloney/imgsizer](https://github.com/ctmaloney/imgsizer)

---

# The Tags


    {exp:imgsizer:size src="/images/news/moped.jpg" width="100"}
      <img src="{sized}" width="{width}" height="{height}" />
      <div style="background-image:url({sized}); width:{width}px; height:{height}px;"></div>
    {/exp:imgsizer:size}

alternatively leave the tag empty and the script will output an image tag for you.

	{exp:imgsizer:placeholder size_src="/images/news/moped.jpg" width="100"}{/exp:imgsizer:placeholder}
---

#Parameters

alt - (string) pass through value for output image tag
cache_path - (string) physical path to the cache folder
cache_url - (string) absolute url to the cache folder
class - (string) pass through value for output image tag
color - (string) 6 character hex code for color of placeholder
height - (integer) for height of output image
id - (string) pass through value for output image tag
passthrough - (string) any other parameters to send to output tag e.g. 'data-stuff'
size_src - (string) get the dimensions of another image for creating a placeholder
style - (string) pass through value for output image tag
title - (string) pass through value for output image tag
width - (integer) for width of output image

#Variables
height - (integer) output height of image
sized - (string) output url of cached image
width - (integer) output width of image

# Troubleshooting:

All error messages are logged in the Template Parsing Log.  If you have no output, or unexpected output, enable the Template Parsing Log in your Output and Debugging Preferences.