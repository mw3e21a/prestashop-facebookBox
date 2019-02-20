/**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

// Wait for the DOM to be ready
$(document).ready(function(){
$(function() {
    // Initialize form validation on the registration form.
    // It has the name attribute "registration"
    $("#module_form").validate({
        // Specify validation rules
        rules: {
            FACEBOOKBOX_PAGE_URL: {
                required: true,
                url: true
            }
        },

        messages: {
            FACEBOOKBOX_PAGE_URL: {
                required: "Facebook Page URL can not be empty",
                url: "Facebook Page URL is invalid"
            }
        },

        submitHandler: function(form) {
            form.submit();
        }
     });
    });
});

