2.0.0 / 16-04-18
================

 * user additionalConfig instead of additionalAttributes for passing options
   to the image renderer.
 * some refactoring
 * can support multiple breakpoints now when using manual config

1.0.3 / 15-12-28
=================

  * Make this a real fork and use c1_fluid_styled_responsive_images as EXTKEY now (mmunz)
  * Allow to force a image_format when rendering (mmunz)
  * allow to give a hint on how big the image will be using additionalParameters,
    this can for example be used by images rendered using the GalleryProcessor. (mmunz)

1.0.2 / 15-11-21
==================

  * 2015-11-21  4bcd1ca  [BUGFIX] Set alt and title from merged properties (Alexander Schnitzler)

1.0.1 / 15-10-29
==================

  * 2015-10-29  4ce3c44  [!!!][TASK] Replace srcsetCandidate with srcset configuration (Alexander Schnitzler)
  * 2015-10-29  8db07a0  [!!!][TASK] Replace mediaQuery with sizes configuration (Alexander Schnitzler)
  * 2015-10-29  d5825fc  [!!!][TASK] Remove default sourceCollection configuration (Alexander Schnitzler)
  * 2015-10-29  c47de08  [TASK] Add basic documentation structure for changelogs (Alexander Schnitzler)
  * 2015-10-29  139eee4  [BUGFIX] Always create sizes attribute, even if it is empty (Alexander Schnitzler)
  * 2015-10-29  c40be71  [BUGFIX] Avoid creating large images if not possible (Alexander Schnitzler)
  * 2015-10-29  e426f7e  [BUGFIX] Ignore height of original image processing configuration (Alexander Schnitzler)

1.0.0 / 15-10-26
==================

  * 2015-10-26  ee9b5c4  Initial commit
