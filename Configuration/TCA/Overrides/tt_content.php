<?php

// Add image_format and image_rows to TCA
$additionalColumns = [
    'image_format' => [
        'exclude' => true,
        'label' => 'LLL:EXT:fluid_styled_responsive_images/Resources/Private/Language/TCA.xlf:image_format_formlabel',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
        ]
    ],
    'image_rows' => array(
        'exclude' => true,
        'label' => 'LLL:EXT:fluid_styled_responsive_images/Resources/Private/Language/TCA.xlf:image_rows_formlabel',
        'config' => array(
            'type' => 'check',
            'items' => array(
                '1' => array(
                    '0' => 'LLL:EXT:fluid_styled_responsive_images/Resources/Private/Language/TCA.xlf:image_rows.1.0'
                )
            ),
            'default' => 1,
        )
    ),
];
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $additionalColumns);
