<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

return [
    'auth', // default extension `auth`, import extension from DOC_ROOT . '/src/extensions/auth/src/extension.php'
    'cms',  // default extension `cms`, import extension from DOC_ROOT . '/src/extensions/cms/src/extension.php'

    // Include here the entry file of other extensions.
    // Note: the entry file must return the class name of the importing Extension
    // for example:
    // '/path-to-extension/extension-name/src/extension.php',
    // DOC_ROOT . '/custom-path/custom-name/src/import.php',
];