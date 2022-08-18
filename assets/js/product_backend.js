
/**
 * Initialize the iframe modal
 * @param {String} modalId
 * @param {String} url
 * @param {Number} width
 * @param {Number} height
 */
function initIframeModal(modalId, url, width, height) {
    if(!Joomla.Bootstrap) {
        jQuery('#'+modalId).on('show.bs.modal', function() {
            jQuery('body').addClass('modal-open');
            let modalBody = jQuery(this).find('.modal-body');
            modalBody.find('iframe').remove();
            modalBody.prepend('<iframe class="iframe" src="'+url+'" name="" height="'+height+'" width="'+width+'"></iframe>');
        }).on('shown.bs.modal', function() {
            var modalHeight = jQuery('div.modal:visible').outerHeight(true),
                modalHeaderHeight = jQuery('div.modal-header:visible').outerHeight(true),
                modalBodyHeightOuter = jQuery('div.modal-body:visible').outerHeight(true),
                modalBodyHeight = jQuery('div.modal-body:visible').height(),
                modalFooterHeight = jQuery('div.modal-footer:visible').outerHeight(true),
                padding = document.getElementById(modalId).offsetTop,
                maxModalHeight = (jQuery(window).height()-(padding*2)),
                modalBodyPadding = (modalBodyHeightOuter-modalBodyHeight),
                maxModalBodyHeight = maxModalHeight-(modalHeaderHeight+modalFooterHeight+modalBodyPadding);
            var iframeHeight = jQuery('.iframe').height();
            if (iframeHeight > maxModalBodyHeight){
                jQuery('.modal-body').css({'max-height': maxModalBodyHeight, 'overflow-y': 'auto'});
                jQuery('.iframe').css('max-height', maxModalBodyHeight-modalBodyPadding);
            }
        }).on('hide.bs.modal', function () {
            jQuery('body').removeClass('modal-open');
            jQuery('.modal-body').css({'max-height': 'initial', 'overflow-y': 'initial'});
            jQuery('.modalTooltip').tooltip('destroy');
        });
    }
}