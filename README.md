The ImageSizer plugin will resize any JPG, GIF or PNG image to the size specified
and cache the resized image to the cache folder.

If you update the original image a new resized version will be created. 


# History

Originally from [http://devot-ee.com/add-ons/image-sizer](http://devot-ee.com/add-ons/image-sizer) and then forked from [https://github.com/ctmaloney/imgsizer](https://github.com/ctmaloney/imgsizer)

---

# The Tag


    {exp:imgsizer:size src="/images/news/moped.jpg" width="100"}
      <img src="{sized}" width="{width}" height="{height}" />
      <div style="background-image:url({sized}); width:{width}px; height:{height}px;"></div>
    {/exp:imgsizer:size}

alternatively leave the tag empty and the script will output an image tag for you.

---

# Tag Parameters

**auto=** [OPTIONAL]

the size of the longest side. If the image is landscape, then this sets the width, else it sets the height.

**autoheight=** [OPTIONAL] 

The height the img should be adjusted to fit. When using this parameter use the 'width' parameter as a max width to control when the image will be cropped.  If the resized width is greater than the given width the image will be cropped, if less it will be left alone.  This setting is very useful for creating image galleries where portrait images should only be resized not cropped.

**base_cache=** [OPTIONAL] 

the base cache folder is where all your cache images are stored within sub directories of your base cache folder by default it is "/web/htdocs/lumis.com/images/sized/" you can change this to anything you wish as long as it points to a folder structure in your sites document root 

**base_path=** [OPTIONAL]

by default the base_path is set by ExpressionEngine to your webroot you may override this by altering this value to something like "/web/htdocs/lumis.com/" 

**cache=** [OPTIONAL]

allows you to turn off image caching (not a good idea) setting this to "no" means your images will be reprocessed everytime the page is loaded (caching is on by default)

**class=, alt=, id=, style=, title=** [OPTIONAL]

pass through html parameters, when the imgsizer tag is empty the auto generated output will include these parameters.

**greyscale=** [OPTIONAL]

if set to yes imagesizer will convert color images to greyscale
    
**height=** [OPTIONAL] 

the height you wish the image resized to. The width is resized proportionately.

**quality=** [OPTIONAL]

only used if image is JPG ranges from 0 (worst quality, smaller file) to 100 (best quality, biggest file). The default is the default value is (100).

**remote_pass=** [OPTIONAL]

HTTP Auth credential used for retrieving a remote file.

**remote_user=** [OPTIONAL]

HTTP Auth credential used for retrieving a remote file.

**responsive=(yes/no)** [OPTIONAL]

Create several smaller copies of the image and serve them to the end user by embedding in a SVG container using media queries. (NOT compatible with IE8!)

**src=** [REQUIRED] 

the relative path to the image or the URL to the image. /images/news/moped.jpg  or  http://www.lumis.com/images/news/moped.jpg

**width=** [OPTIONAL] 

the width you wish the image resized to. The height is resized proportionately.

NOTE:
* if you use only width or only height the image will be scaled to match that width or height proportionately. 
* if you use auto, image will be scaled to the longest side proportionately. 
* if you use both width and height the image will be cropped from center to that width and height.
* if "width" is = to "height" the image will be cropped from image center to make a square sized image.

# Troubleshooting:

All error messages are logged in the Template Parsing Log.  If you have no output, or unexpected output, enable the Template Parsing Log in your Output and Debugging Preferences.