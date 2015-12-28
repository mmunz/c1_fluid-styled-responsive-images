# fluid-styled-responsive-images

** Note: This is an experiment and not production ready. **

This fork of https://github.com/alexanderschnitzler/fluid-styled-responsive-images
is an attempt to get better responsive images by being aware of the column
size/width, in which the element will be displayed.

## Automatic width detection

If a basic grid is built with c1_fce_grid (https://github.com/mmunz/c1_fce_grid),
then the ImageRenderer tries to recursively get the column
widths for different breakpoints and create the sizes attribute for the
responsive image.  E.g: The Image is in a col-md-4 column, then the following
sizes attribute would be added: "(min-width: 992x) 33vw, 100vw"

## Manual hints using additionalAttributes

When rendering an image from fluid using the f:media viewhelper some
additionalParameters can be passed to the image renderer:

* vw - the viewport width of the image at the given breakpoint (integer)
* breakpoint - Breakpoint, at which the above vw width is used (string)
* image_ratio - Force a predefined image ratio (integer)

(Yes, this allows only for one breakpoint at the moment)

```
<f:media
    file="{img.media}"
    width="{img.dimensions.width}"
    alt="foo"
    title="{img.media.title}"
    additionalAttributes="{vw: 33.33, breakpoint: 'sm', image_ratio: image_ratio}"
/>
In this example the image will be 33.33% width at screens larger than 'sm'

```

## styles.content settings

You need some settings in styles.content constants to allow larger images and
enable responsive image rendering:

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

