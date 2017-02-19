<?php
/** @var string $_EXTKEY */

$EM_CONF[$_EXTKEY] = array(
    'title' => 'C1 Fluid Styled Responsive Images',
    'description' => 'Enables creation of responsive images for fluid styled content elements. Customized to be row-aware together with c1_fce_grid grids.',
    'category' => 'fe',
    'version' => '2.0.0',
    'state' => 'beta',
    'uploadfolder' => false,
    'clearcacheonload' => true,
    'author' => 'Manuel Munz',
    'author_email' => 't3dev@comuno.net',
    'constraints' => [
        'depends' => [
            'php' => '5.5.0-8.99.99',
            'typo3' => '7.5.0-8.99.99',
            'c1_fce_grid' => '0.1.0-1.0.0'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
);
