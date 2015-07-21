/**
 *               _
 *              | |
 *     __ _   _ | | _  _   ___  _ __
 *    / _` | / || || || | / _ \| '  \
 *   | (_| ||  || || || ||  __/| || |
 *    \__,_| \__,_|\__, | \___||_||_|
 *                 |___/
 *
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O <info@h-o.nl>
 */

jQuery(function($){
    var profileType = $('#adyen_subscription_type');
    var tab = $('#product_info_tabs_adyen_subscription_profile_tab_content');
    var profileTemplate = tab.find('.product-fieldset-template')
        .parents('.profile-fieldset-container');
    var sortableArea = $('#subscriptions_fieldset');
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


    // Make profiles sortable
    sortableArea.sortable({
        placeholder: 'ui-state-highlight',
        items: '.profile-fieldset-container'
    });

    $(document).on('click', '#subscriptions_fieldset .product-profile-delete', function(){
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
                    '[new' + newProfileId + ']'
                )
            );

            elem.attr('id',
                elem.attr('id').replace(
                    '[template]',
                    '[new' + newProfileId + ']'
                )
            );
            elem.prop('disabled', false);
        });

        //Add dynamic tax calculation
        newProfile.find('#dynamic-tax-profile-template')
            .attr('id','dynamic-tax-product_profile[new'+newProfileId+'][price]');
        dynamicTaxes.push('product_profile[new'+newProfileId+'][price]');
        newProfile.find('.price-tax-calc').on('change keyup', recalculateTax);

        newProfile.show();
        newProfile.find('.profile-fieldset').removeClass('product-fieldset-template');
        sortableArea.append(newProfile);
    });
});
