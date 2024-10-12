var selUser = jQuery('select[name="sel_user[]"]').bootstrapDualListbox({
    nonSelectedListLabel: 'List Users',
    selectedListLabel: 'Selected Users',
    infoText: 'Showing all {0}',
    infoTextEmpty: 'Empty list',
    filterPlaceHolder: 'Filter',
    selectorMinimalHeight: 200
});

var selrole = jQuery('select[name="sel_role[]"]').bootstrapDualListbox({
    nonSelectedListLabel: 'List Roles',
    selectedListLabel: 'Selected Roles',
    infoText: 'Showing all {0}',
    infoTextEmpty: 'Empty list',
    filterPlaceHolder: 'Filter',
    selectorMinimalHeight: 200
});

jQuery('#aru_notice_dismiss').on('click', function (event) {
    event.preventDefault();
    jQuery('#aru_message').fadeTo(100, 0, function () {
        jQuery('#aru_message').slideUp(100, function () {
            jQuery('#aru_message').remove();
        });
    });
});