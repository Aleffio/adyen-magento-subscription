/**
 * Ho_Recurring
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the H&O Commercial License
 * that is bundled with this package in the file LICENSE_HO.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.h-o.nl/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@h-o.com so we can send you a copy immediately.
 *
 * @category    Ho
 * @package     Ho_Recurring
 * @copyright   Copyright © 2015 H&O (http://www.h-o.nl/)
 * @license     H&O Commercial License (http://www.h-o.nl/license)
 * @author      Maikel Koek – H&O <info@h-o.nl>
 */

var $_profilesFieldsetId    = 'recurring_profiles_fieldset';
var $_dummyFieldsetClass    = 'dummy-fieldset';
var $_dummyProfileId        = false;

jQuery(function($){
    _getDummyFieldset().prev().addClass($_dummyFieldsetClass);
});

function addRecurringProductProfile()
{
    var $fieldset = _getParentFieldset();
    var $profileFieldset = _getDummyFieldset();
    var $header = $profileFieldset.prev();

    var $newProfileFieldset = $profileFieldset.clone();
    var newProfileId = _getNewDummyProfileId();

    $newProfileFieldset.find('select, input').each(function() {
        jQuery(this).attr('name',
            jQuery(this).attr('name').replace(
                'dummy_profile\[\]',
                'product_profile\[' + newProfileId + '\]'
            )
        );
    });

    $fieldset.append($newProfileFieldset.removeClass($_dummyFieldsetClass));
}

function _getParentFieldset()
{
    return jQuery('#' + $_profilesFieldsetId);
}
function _getDummyFieldset()
{
    return _getParentFieldset().find('.' + $_dummyFieldsetClass);
}

function _getNewDummyProfileId()
{
    if ($_dummyProfileId === false) {
        $_dummyProfileId = 1;
    }
    else {
        $_dummyProfileId++;
    }

    return 'new-' + $_dummyProfileId;
}
