<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'c1_fluid_styled_responsive_images',
    'Configuration/TypoScript',
    'Fluid Styled Responsive Images'
);

// Add TCEFORM Config
$tceformConfig = \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TSConfig/tceform.ts');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($tceformConfig);
