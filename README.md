The ImageSizer plugin will resize any JPG, GIF or PNG image to the size specified
and cache the resized image to the cache folder.

It can also output a placeholder image appropriately scaled which is ideal for
lazy-loading situation.

# History
Originally from [http://devot-ee.com/add-ons/image-sizer](http://devot-ee.com/add-ons/image-sizer)
and then forked from [https://github.com/ctmaloney/imgsizer](https://github.com/ctmaloney/imgsizer)
and *then* forked form [https://github.com/meatpaste/imgsizer](https://github.com/meatpaste/imgsizer)

Special note: This stage would not have been reached without the special efforts of [https://github.com/meatpaste](@meatpaste).

## Versions
EE3 - [Latest Release](https://github.com/meatpaste/imgsizer/releases/latest)

EE2 - [4.0.2](https://github.com/meatpaste/imgsizer/releases/tag/4.0.2)

## The Tags
Resize an image and output the resized path in your own HTML:

```
{exp:imgsizer:size src="/images/news/moped.jpg" width="100"}
  <img src="{sized}" width="{width}" height="{height}" />
  <div style="background-image:url({sized}); width:{width}px; height:{height}px;"></div>
{/exp:imgsizer:size}
```

Produce a placeholder using a full sized image to calculate proportions:

```
{exp:imgsizer:placeholder size_src="/images/news/moped.jpg" width="100"}{/exp:imgsizer:placeholder}
```

## Parameters
- 'alt' - (string) pass through value for output image tag
- 'cache_path' - (string) physical path to the cache folder
- 'cache_url' - (string) absolute url to the cache folder
- 'class' - (string) pass through value for output image tag
- 'color' - (string) 6 character hex code for color of placeholder
- 'height' - (integer) for height of output image
- 'id' - (string) pass through value for output image tag
- 'passthrough' - (string) any other parameters to send to output tag e.g. 'data-stuff'
- 'size_src' - (string) get the dimensions of another image for creating a placeholder
- 'src' - (string) the path to the image you wish to resize
- 'style' - (string) pass through value for output image tag
- 'title' - (string) pass through value for output image tag
- 'width' - (integer) for width of output image

#Variables
- 'height' - (integer) output height of image
- 'sized' - (string) output url of cached image
- 'width' - (integer) output width of image


#The RTE Button (EE3 Only)
Development of this feature has been sponsored by [Triad. Think Creative](http://triad.uk.com)

Installation automatically disables the built in RTE upload button and replaces it
with an Imgsizer button using the same icon. The button allows inserting
of Gif/Jpeg/PNG files with a special class which automatically swaps in Imgsizer
when the entry is displayed. The thumbnail of the image is displayed in the RTE
for speed.

Hovering over the image in the RTE shows buttons for
floating left and right as well as changing of the width and deleting.

Any other file types will display a hyperlink when selected in the file chooser.

## Troubleshooting:
All error messages are logged in the Template Parsing Log.  If you have no output,
or unexpected output, enable the Template Parsing Log in your Output and Debugging
Preferences. If you are still stuck please raise an issue using the
[GitHub Issues page](https://github.com/meatpaste/imgsizer/issues/)
