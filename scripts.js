
    /**
     * Plugin Name: Simple Settings
     * Plugin URI: http://www.ilikemustard.com
     * Description: JavaScript used to manipulate the admin interface.
     * Version: 1.2
     *
     * Author: Jimmy K.
     * Author URI: http://www.ilikemustard.com
     */

    jQuery(document).ready(function() {

        ilmss_updatePostBoxText();
        ilmss_addMoveToTrashConfirmationAlert();
        ilmss_initValuesMetaBox();

    });

    /**
     * Set the postbox container text values. This could be done in PHP by hooking
     * the 'gettext' action, but using jQuery has proven to be more reliable.
     *
     * @return void
     * @author Jimmy K.
     */

    function ilmss_updatePostBoxText()
    {

        // Replace 'Publish' with 'Actions'..
        jQuery('#submitdiv h3.hndle span').html('Actions');

    }

    /**
     * Add a confirmation popup when clicking the 'Move to Trash' button.
     *
     * @return void
     * @author Jimmy K.
     */

    function ilmss_addMoveToTrashConfirmationAlert()
    {

        jQuery('#major-publishing-actions #delete-action a').click(function(e) {

            // Ask for confirmation..
            $bConfirm = confirm('Are you sure?');

            if (!$bConfirm) {
                // Didn't confirm, prevent the click event..
                e.preventDefault();
            }

        });

    }

    /* ================================================== */
    /* Meta Box Functions
    /* ================================================== */

    /**
     * Initialize the 'Values' meta box.
     *
     * @return void
     * @author Jimmy K.
     */

    function ilmss_initValuesMetaBox()
    {

        // Get the dropdown element..
        $oTypeElement = jQuery('#ilmss_type');

        if ($oTypeElement.length > 0) {

            $oTypeElement.change(function() {
                // Dropdown value changed, update the form elements visibility..
                ilmss_showValueElement(jQuery(this).attr('value'));
            });

            // Show the element for the selected type..
            ilmss_showValueElement($oTypeElement.attr('value'));

        }

    }

    /**
     * Hide all of the 'Values' form elements.
     *
     * @return void
     * @author Jimmy K.
     */

    function ilmss_hideAllValueElements()
    {

        // Hide the elements wrapper..
        jQuery('.valueWrapper').css('display', 'none');

        // Hide the form elements..
        jQuery('.textareaValueWrapper').css('display', 'none');
        jQuery('.booleanValueWrapper').css('display', 'none');

    }

    /**
     * Show the specified 'Value' form element.
     *
     * @return void
     * @author Jimmy K.
     */

    function ilmss_showValueElement($sType)
    {

        // Hide all the value elements.,
        ilmss_hideAllValueElements();

        if ($sType != '') {

            // Show the value wrapper..
            jQuery('.valueWrapper').css('display', '');

            if ($sType == 'textarea') {

                // Show the textarea..
                jQuery('.textareaValueWrapper').css('display', '');

            } else if ($sType == 'boolean') {

                // Show the radio group..
                jQuery('.booleanValueWrapper').css('display', '');

            }

        }

    }
