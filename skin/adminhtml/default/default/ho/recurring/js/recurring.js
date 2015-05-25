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
jQuery(function($){
    var profileType = $('#ho_recurring_type');
    var tab = $('#product_info_tabs_ho_recurring_profile_tab_content');
    var profileTemplate = tab.find('.product-fieldset-template')
        .parents('.profile-fieldset-container');
    var sortableArea = $('#recurring_profiles_fieldset');
    var addButton = tab.find('.product-profile-add');

    var newProfileId = 0;

    //hide the template
    profileTemplate.hide().find('textarea, input, select').prop('disabled', true);


    $(document).on('change', profileType, function(){

        var container = $('.profile-fieldset-container').not(profileTemplate);

        if (profileType.val() <= 0) {
            addButton.hide();
            container.hide().find('textarea, input, select').prop('disabled', true);
        } else {
            addButton.show();
            container.show().find('textarea, input, select').prop('disabled', false);
        }
    }).trigger('change');


    // Make profiles sortablev
    sortableArea.sortable({
        placeholder: 'ui-state-highlight',
        items: '.profile-fieldset-container'
    });

    $(document).on('click', '#recurring_profiles_fieldset .product-profile-delete', function(){
        $(this).parents('.profile-fieldset-container').remove();
    });

    addButton .on('click', function(){
        var newProfile = profileTemplate.clone();
        newProfileId++;

        newProfile.find('select, input').each(function() {
            var elem = $(this);

            elem.attr('name',
                elem.attr('name').replace(
                    '[template]',
                    '[' + newProfileId + ']'
                )
            );

            elem.attr('id',
                elem.attr('id').replace(
                    '[template]',
                    '[' + newProfileId + ']'
                )
            );
            elem.prop('disabled', false);
        });

        //Add dynamic tax calculation
        newProfile.find('#dynamic-tax-profile-template')
            .attr('id','dynamic-tax-product_profile['+newProfileId+'][price]');
        dynamicTaxes.push('product_profile['+newProfileId+'][price]');
        newProfile.find('.price-tax-calc').on('change keyup', recalculateTax);

        newProfile.show();
        newProfile.find('.profile-fieldset').removeClass('product-fieldset-template');
        sortableArea.append(newProfile);
    });
});
