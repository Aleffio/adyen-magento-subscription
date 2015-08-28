/**
 *                       ######
 *                       ######
 * ############    ####( ######  #####. ######  ############   ############
 * #############  #####( ######  #####. ######  #############  #############
 *        ######  #####( ######  #####. ######  #####  ######  #####  ######
 * ###### ######  #####( ######  #####. ######  #####  #####   #####  ######
 * ###### ######  #####( ######  #####. ######  #####          #####  ######
 * #############  #############  #############  #############  #####  ######
 *  ############   ############  #############   ############  #####  ######
 *                                      ######
 *                               #############
 *                               ############
 *
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O E-commerce specialists B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O E-commerce specialists B.V. <info@h-o.nl>
 */

jQuery(function($){
    var subscriptionType = $('#adyen_subscription_type');
    var tab = $('#product_info_tabs_adyen_subscription_tab_content');
    var subscriptionTemplate = tab.find('.product-fieldset-template')
        .parents('.subscription-fieldset-container');
    var sortableArea = $('#subscription_fieldset');
    var addButton = tab.find('.product-subscription-add');

    var newSubscriptionId = 0;

    //hide the template
    subscriptionTemplate.hide().find('textarea, input, select').prop('disabled', true);

    $(document).on('change', subscriptionType, function(){
        var container = $('.subscription-fieldset-container').not(subscriptionTemplate);

        if (subscriptionType.val() <= 0) {
            addButton.hide();
            container.hide().find('textarea, input, select').prop('disabled', true);
        } else {
            addButton.show();
            container.show().find('textarea, input, select').prop('disabled', false);
        }
    }).trigger('change');

    // Make subscriptions sortable
    sortableArea.sortable({
        placeholder: 'ui-state-highlight',
        items: '.subscription-fieldset-container'
    });

    // Show noti
    $(document).on('change', '#subscription_fieldset :input', function(){
        $(this).parents('.subscription-fieldset-container').find('.adyen_notice').show();
    });

    addButton.on('click', function(){
        var newSubscription = subscriptionTemplate.clone();
        newSubscriptionId++;

        newSubscription.find('textarea, select, input').each(function() {
            var elem = $(this);

            elem.attr('name',
                elem.attr('name').replace(
                    '[template]',
                    '[new' + newSubscriptionId + ']'
                )
            );

            elem.attr('id',
                elem.attr('id').replace(
                    '[template]',
                    '[new' + newSubscriptionId + ']'
                )
            );
            elem.prop('disabled', false);
        });

        sortableArea.append(newSubscription);

        //Add dynamic tax calculation
        newSubscription.find('[id="dynamic-tax-product_subscription[template][price]"]')
            .attr('id','dynamic-tax-product_subscription[new'+newSubscriptionId+'][price]');

        if (typeof dynamicTaxes != 'undefined') {
            dynamicTaxes.push('product_subscription[new'+newSubscriptionId+'][price]');
        }
        newSubscription.find('.price-tax-calc').on('change keyup', recalculateTax);

        newSubscription.show();
        newSubscription.find('.subscription-fieldset').removeClass('product-fieldset-template');
    });

    sortableArea.find('.subscription-fieldset').each(function(){
        var id = $(this).attr('id');
        if (id.indexOf('[template]') > 0) {
            return;
        }

        if (typeof dynamicTaxes != 'undefined') {
            dynamicTaxes.push(id+'[price]');
        }
        $('[id="'+id+'[price]"]').on('change keyup', function(){
            recalculateTax();
        });

        console.log('[id="'+id+'[update_price]"]');
        $('[id="'+id+'[update_price]"]', function(){
            console.log(this);
            $(this).prop('disabled', true);
        });
    });
});
