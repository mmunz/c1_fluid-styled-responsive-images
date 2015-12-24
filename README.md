# fluid-styled-responsive-images

** Note: This is an experiment and not production ready. **

This fork of https://github.com/alexanderschnitzler/fluid-styled-responsive-images
is an attempt to get better responsive images by being aware of the column
size/width, in which the element will be displayed. This is a very simple
approach to this problem:

A basic grid is built with c1_fce_grid (https://github.com/mmunz/c1_fce_grid).
When an image is rendered, the ImageRenderer tries to recursively get the column
widths for different breakpoints and create the sizes attribute for the
responsive image.  E.g: The Image is in a col-md-4 column, then the following
sizes attribute would be added: "(min-width: 992x) 33vw, 100vw"

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

