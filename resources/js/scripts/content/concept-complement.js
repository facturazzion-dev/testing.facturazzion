'use strict';

var conceptComplementDropdown = $('.dropdown-concept-complement .dropdown-item'),
    removeComplementButton = $('.concept-complement .close');

/* Displays concept complements */
conceptComplementDropdown.on('click', function () {
    var $this = $(this),
        selectedComplement = $this.data('complement');

    if (selectedComplement) {
        //hides dropdown
        $this.closest('.repeater-wrapper')
            .find('.dropdown-concept-complement')
            .addClass('d-none');
        //shows complement concept form
        $this.closest('.repeater-wrapper')
            .find('#cc-' + selectedComplement)
            .removeClass('d-none');
    }
});
removeComplementButton.on('click', function () {
    var $this = $(this),
        conceptComplementForm = $this.closest('.repeater-wrapper').find('.concept-complement'),
        conceptComplementDropdown = $this.closest('.repeater-wrapper').find('.dropdown-concept-complement');
    // resets and hides complement concept form
    conceptComplementForm.find('input').val('');
    conceptComplementForm.addClass('d-none');
    //shows dropdown
    conceptComplementDropdown.removeClass('d-none');
})