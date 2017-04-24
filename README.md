# fluid-styled-responsive-images

** discontinued. While this solution worked for me it was to difficult and i'm trying a new approach using lazysizes.js now **


** Note: This is an experiment and not production ready. **

This fork of https://github.com/alexanderschnitzler/fluid-styled-responsive-images
is an attempt to get better responsive images by being aware of the column
size/width in which the element will be displayed.

## Automatic width detection

If a basic grid is built with c1_fce_grid (https://github.com/mmunz/c1_fce_grid),
then the ImageRenderer tries to recursively get the column
widths for different breakpoints and create the sizes attribute for the
responsive image.  E.g: The Image is in a col-md-4 column, then the following
sizes attribute would be added: "(min-width: 992x) 33vw, 100vw"

## Manual hints using additionalAttributes

When rendering an image from fluid using the f:media viewhelper some
additional options can be passed to the image renderer:

* image_format - Force a image aspect ratio, e.g. 2 = 1/2 ratio (float)
* respImg
  * breakpoint
    * vw - the viewport width of the image at the given breakpoint (integer)
    * image_format - image ratio (float)
```
<f:media
    file="{img.media}"
    width="{img.dimensions.width}"
    alt="foo"
    title="{img.media.title}"
    additionalConfig="{image_format: 2, respImg: {xxs: {image_format: '1.3'}, xs: {image_format: '1.3'}, sm: {image_format: '1.5'}}}"
/>
```
In this example the image will be 33.33% width at screens larger than 'sm' and
use a default image ratio of 1/2. On small screens (sm) we use a ratio of 1/1.5.

It is also possible to use additionalAttributes to add the sizes attribute to
rendered images, e.g.

```
<f:media
    file="{mediaElement}"
    title="{mediaElement.originalResource.title}"
    alt="{mediaElement.originalResource.alternative}"
    width="1920"
    additionalAttributes="{sizes: '(min-width: 62em) 33vw, (min-width: 48em) 50vw, 100vw'}"
/>
```

If both additionalAttributes and additionalConfig is used then the sizes
option from additionalAttributes takes precedence.

## styles.content settings

You need some settings in styles.content constants to allow larger images and
enable responsive image rendering:

```
styles.content {
    imgtext {
        responsive = 1
        layoutKey = srcset
    }
	textmedia {
		maxW = 1920
		maxWInText = 940
    }
}
```

## Setting image ratios
We use TYPO3's own image ratio array in
$GLOBALS['TCA']['sys_file_reference']['columns']['crop']['config']['ratios'].

We overwrite and extend this in Classes/Overrides/tt_content.php. To add new
ratios: Add your own overwrites in the provider extension.
