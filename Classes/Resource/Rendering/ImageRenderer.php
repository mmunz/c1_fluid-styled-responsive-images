<?php

namespace C1\FluidStyledResponsiveImages\Resource\Rendering;

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\Rendering\FileRendererInterface;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Service\TypoScriptService;
use TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use FluidTYPO3\Fluidcontent\Service\ConfigurationService;

/**
 * Class ImageRenderer
 * @package C1\FluidStyledResponsiveImages\Resource\Rendering
 */
class ImageRenderer implements FileRendererInterface {

    /**
     * @var TypoScriptService
     */
    protected $typoScriptService;

    /**
     * @var TagBuilder
     */
    protected $tagBuilder;

    /**
     * @var array
     */
    protected $possibleMimeTypes = [
        'image/jpg',
        'image/jpeg',
        'image/png',
        'image/gif',
    ];

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var \TYPO3\CMS\Core\Resource\File
     */
    protected $imageFile;

    /**
     * @var FileInterface
     */
    protected $originalFile;

    /**
     * @var array
     */
    protected $sizes;

    /**
     * @var array
     */
    protected $srcset;

    /**
     * @var array
     */
    protected $additionalConfig;

    /**
     * @var array
     */
    protected $additionalAttributes;

    /**
     * @var array
     */
    protected $defaultProcessConfiguration;

    /**
     * @var \TYPO3\CMS\Extbase\Service\FlexFormService
     */
    protected $flexFormService;

    /**
     * @return ImageRenderer
     */
    public function __construct() {
        $this->settings = [];
        $this->typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $this->tagBuilder = GeneralUtility::makeInstance(TagBuilder::class);
        $this->getConfiguration();
    }

    /**
     * @return int
     */
    public function getPriority() {
        return 5;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Service\FlexFormService $flexFormService
     */
    public function injectFlexFormService(\TYPO3\CMS\Extbase\Service\FlexFormService $flexFormService) {
        $this->flexFormService = $flexFormService;
    }

    /**
     * @param FileInterface $file
     * @return bool
     */
    public function canRender(FileInterface $file) {
        return TYPO3_MODE === 'FE' && in_array($file->getMimeType(), $this->possibleMimeTypes, true);
    }

    /**
     * @param Array $options
     * @return void
     */
    protected function init($file, $width, $height, $options) {
        $this->srcset = [];
        $this->originalFile = $file;
        if ($file instanceof FileReference) {
            $this->imageFile = $file->getOriginalFile();
        } else {
            $this->imageFile = $file;
        }

        try {
            $this->defaultProcessConfiguration = [];
            $this->defaultProcessConfiguration['width'] = (int) $width;
            $this->defaultProcessConfiguration['height'] = (int) $height;
            $this->defaultProcessConfiguration['crop'] = $this->imageFile->getProperty('crop');
        } catch (\InvalidArgumentException $e) {
            $this->defaultProcessConfiguration['crop'] = '';
        }

        is_array($options['additionalConfig']) ? $this->additionalConfig = $options['additionalConfig'] : null;

        if (is_array($options['additionalAttributes'])) {
            $this->additionalAttributes = $options['additionalAttributes'];
            if (array_key_exists('sizes', $this->additionalAttributes)) {
                $this->sizes = $this->additionalAttributes['sizes'];
            }
        }
    }

    /**
     * @return void
     */
    protected function calculateSizes() {
        $fceUid = $this->originalFile->getProperty('uid_foreign');
        $objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        if ($this->flexFormService === NULL) {
            $this->flexFormService = $objectManager->get('TYPO3\CMS\Extbase\Service\FlexFormService');
        }
        $parentTree = $this->getParents($fceUid);
        $this->getSizes($parentTree);
        //$sizesArray = array_reverse($this->getSizes($parentTree));
//        foreach ($this->sizes as $size) {
//            if ($size['breakpoint'] !== '0') {
//                $this->sizes[] = sprintf(
//                        '(min-width: %dpx) %dvw ', $size['breakpoint'], $size['vw']
//                );
//            }
//        }
        //$this->sizes[] = "100vw";
    }

    /**
     * @return string
     */
    protected function formatSizes() {
        $formatted = [];
        foreach ($this->sizes as $size) {
            if ($size['breakpoint'] !== '0') {
                $formatted[] = sprintf('(min-width: %dpx) %dvw', $size['breakpoint'], $size['vw']);
            }
        }
        $formatted[] = "100vw";
        return implode(', ', $formatted);
    }

    /**
     * Creates the srcset line and the needed images
     * @return void
     */
    protected function createSrcSet() {
        foreach ($this->settings['sourceCollection'] as $key => $configuration) {
            try {
                if (!is_array($configuration)) {
                    throw new \RuntimeException();
                }
                // skip the default key
                if ($key === 'default') {
                    continue;
                }
                if ((int) $width > 0 && (int) $configuration['width'] > (int) $width) {
                    throw new \RuntimeException();
                }
                $localProcessingConfiguration = $this->defaultProcessConfiguration;

                if ($this->additionalConfig['respImg'][$key]['image_format'] > 0) {
                    $localProcessingConfiguration['width'] = intval($configuration['width']) . "c";
                    $localProcessingConfiguration['height'] = round(intval($configuration['width']) / $this->additionalConfig['respImg'][$key]['image_format']) . "c";
                } else {
                    $localProcessingConfiguration['width'] = intval($configuration['width']) . 'm';
                }
                if ($this->settings['debug'] > 0) {
                    // add width to the image
                    $localProcessingConfiguration['additionalParameters'] = '-pointsize 40 -stroke white -gravity Center -annotate +0+0 ' .
                            $localProcessingConfiguration['width'] . ' -gravity NorthWest';
                }

                $processedFile = $this->imageFile->process(
                        ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, $localProcessingConfiguration
                );

                $url = $GLOBALS['TSFE']->absRefPrefix . $processedFile->getPublicUrl();

                $this->srcset[] = $url . rtrim(' ' . $configuration['srcset'] ? : '');
            } catch (\Exception $ignoredException) {
                continue;
            }
        }
    }

    /**
     * Renders the image tag
     * @return void
     */
    protected function renderImg() {
        $originalProcessingConfiguration = $this->defaultProcessConfiguration;
        $originalProcessingConfiguration['width'] = isset($this->settings['sourceCollection']['default']['width']) ? $this->settings['sourceCollection']['default']['width'] : 600;

        if ($this->additionalConfig['image_format']) {
            $originalProcessingConfiguration['height'] = round(intval($originalProcessingConfiguration['width']) / $this->additionalConfig['image_format']);
        }

        $processedFile = $this->imageFile->process(
                ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, $originalProcessingConfiguration
        );
        $src = $processedFile->getPublicUrl();

        $altText = $this->imageFile->getProperty('alternative') ? $this->imageFile->getProperty('alternative') : $this->imageFile->getProperty('name');

        $this->tagBuilder->reset();
        $this->tagBuilder->setTagName('img');
        $this->tagBuilder->addAttribute('src', $src);
        $this->tagBuilder->addAttribute('alt', $altText);
        $this->tagBuilder->addAttribute('width', $processedFile->getProperty('width'));
        $this->tagBuilder->addAttribute('height', $processedFile->getProperty('height'));

        if ($this->imageFile->getProperty('title')) {
            $this->tagBuilder->addAttribute('title', $this->imageFile->getProperty('title'));
        }
        if ($this->settings['cssClasses']['img']) {
            $this->tagBuilder->addAttribute('class', $this->settings['cssClasses']['img']);
        }

        if (!empty($this->srcset)) {
            $this->tagBuilder->addAttribute('srcset', implode(', ', $this->srcset));
            $this->tagBuilder->addAttribute('sizes', $this->formatSizes());
        }

        // add additionalAttributes
        if (is_array($this->additionalAttributes)) {
            foreach ($this->additionalAttributes as $key => $value) {
                $this->tagBuilder->addAttribute($key, $value);
            }
        }

        return $this->tagBuilder->render();
    }

    /**
     * @return ContentObjectRenderer
     */
    protected function getTypoScriptSetup() {
        if (!$GLOBALS['TSFE'] instanceof TypoScriptFrontendController) {
            return [];
        }

        if (!$GLOBALS['TSFE']->tmpl instanceof TemplateService) {
            return [];
        }
        return $GLOBALS['TSFE']->tmpl->setup;
    }

    /**
     * @param array containing the parent "tree"
     * @return array sizes array
     */
    protected function getSizes($tree) {
        $this->sizes = [];
        if (\is_array($this->additionalConfig) && $this->additionalConfig['respImg']) {
            foreach ($this->additionalConfig['respImg'] as $key => $value) {
                if ($value['vw']) {
                    $value['breakpoint'] = $this->settings['breakpoints_grid'][$value['breakpoint']];
                    $this->sizes[] = $value;
                }
            }
        }
        $fluxColumn = substr($tree[0]['tx_flux_column'], 6);

        foreach ($tree as $key => $configuration) {
            $breakpoint = $configuration['flexform']['breakpoint'];
            if ($breakpoint && $fluxColumn) {
                $cols = $configuration['flexform']['columns'][$fluxColumn]['column']['cols'];
                if ($cols) {
                    $viewportSize = $cols / 12 * 100;
                    $this->sizes[] = [
                        'breakpoint' => $this->settings['breakpoints_grid'][$breakpoint],
                        'vw' => $viewportSize
                    ];
                }
            }
            $fluxColumn = substr($configuration['tx_flux_column'], 6);
        }

        /* apply some magic to get sizes right for nested grids */
        $lastBreakpoint = FALSE;
        $lastVw = False;
        foreach ($this->sizes as $key => $size) {
            if ($lastBreakpoint && $lastVw) {
                if ($lastBreakpoint < $size['breakpoint']) {
                    $this->sizes[$key]['vw'] = $size['vw'] * $lastVw / 100;
                }
            }
            $lastBreakpoint = $size['breakpoint'];
            $lastVw = $size['vw'];
        }
    }

    /**
     * @param int $uid uid of the child element
     * @return array
     */
    protected function getParentConfig($uid) {
        /* flux configuration of this elements parent element */
        $fluxParent = BackendUtility::getRecord(tt_content, $uid, 'uid, tx_flux_parent, tx_flux_column');
        return $fluxParent;
    }

    /**
     * @param int tt_content $uid uid of the child element where we start to search for parents
     * @return array
     */
    protected function getFlexformData($uid) {
        $record = BackendUtility::getRecord(tt_content, $uid, '*');
        $flexFormConfiguration = $this->flexFormService->convertFlexFormContentToArray($record['pi_flexform']);
        return $flexFormConfiguration;
    }

    /**
     * @param int tt_content $uid uid of the child element where we start to search for parents
     * @return array
     */
    protected function getParents($uid) {
        $parents = [];
        $fluxParent = $this->getParentConfig($uid);
        $fluxParent['flexform'] = $this->getFlexformData($fluxParent['uid']);
        $parents[] = $fluxParent;
        while ((int) $fluxParent['tx_flux_parent'] > 0) {
            $fluxParent = $this->getParentConfig($fluxParent['tx_flux_parent']);
            $fluxParent['flexform'] = $this->getFlexformData($fluxParent['uid']);
            $parents[] = $fluxParent;
        }
        return $parents;
    }

    /**
     * @return void
     */
    protected function getConfiguration() {
        $configuration = $this->typoScriptService->convertTypoScriptArrayToPlainArray($this->getTypoScriptSetup());

        $settings = ObjectAccess::getPropertyPath(
                        $configuration, 'tt_content.textmedia.settings.responsive_image_rendering'
        );
        $settings = is_array($settings) ? $settings : [];

        $this->settings['layoutKey'] = (isset($settings['layoutKey'])) ? $settings['layoutKey'] : 'default';
        $this->settings['debug'] = (isset($settings['debug'])) ? $settings['debug'] : false;
        $this->settings['sourceCollection'] = (isset($settings['sourceCollection']) && is_array($settings['sourceCollection'])) ? $settings['sourceCollection'] : [];
        $this->settings['cssClasses']['img'] = (isset($settings['cssClasses']['img'])) ? $settings['cssClasses']['img'] : false;
        $this->settings['breakpoints_grid'] = (isset($settings['breakpoints_grid'])) ? $settings['breakpoints_grid'] : false;
    }

    /**
     * @param FileInterface $file
     * @param int|string $width TYPO3 known format; examples: 220, 200m or 200c
     * @param int|string $height TYPO3 known format; examples: 220, 200m or 200c
     * @param array $options
     * @param bool $usedPathsRelativeToCurrentScript See $file->getPublicUrl()
     * @return string
     */
    public function render(
    FileInterface $file, $width, $height, array $options = array(), $usedPathsRelativeToCurrentScript = false
    ) {
        $this->init($file, $width, $height, $options);
        if (\is_array($this->additionalConfig) && $file->getProperty('tablenames') === 'tt_content') {
            // calculating sizes only works for content elements in tt_content
            $this->calculateSizes();
        }
        $this->createSrcSet();
        return $this->renderImg();
    }

}
