import Handsontable from 'handsontable';

const tableElement = document.getElementById('newBulkOrdersTable');

if(tableElement) {
  let handsOnTable = new Handsontable(tableElement, {
    data: [['','','', '']],
    height: 320,
    minSpareRows: 20,
    colWidths: [170, 140, 140, 410],
    colHeaders: [
      'Serial Number',
      'PO (Optional)',
      'Claim # (Optional)',
      'Notes',
    ],
    columns: [
      { data: 1, type: 'text' },
      { data: 2, type: 'text' },
      { data: 3, type: 'text' },
      { data: 4, type: 'text' },
    ],
    dropdownMenu: true,
    hiddenColumns: true,
    contextMenu: false,
    filters: true,
    rowHeaders: true,
    manualRowMove: true,
    licenseKey: 'non-commercial-and-evaluation',
  });

  jQuery('#submit-new-orders').on('click', function() {
    let data = handsOnTable.getData();
    var $this = jQuery(this);
    if($this.attr('disabled')) {
      return;
    }
    $this.data('ohtml', $this.html());
    var nhtml = '<i class=\'fas fa-spinner fa-spin tw-mr-2\'></i> ' + this.dataset.buttonSpinner;
    $this.html(nhtml);
    $this.attr('disabled', true);

    jQuery.ajax({
      data: {
        action: 'process_new_bulk_import',
        rows: data,
        companyName: $('#bulk-bus-name').val(),
        streetone: $('#bulk-street1').val(),
        streettwo: $('#bulk-street2').val(),
        city: $('#bulk-city').val(),
        state: $('#bulk-state').val(),
        zip: $('#bulk-zip').val(),
      },
      type: 'POST',
      // eslint-disable-next-line no-undef
      url: iresq_ajax.ajax_url,
      complete: function() {
        jQuery('#new-orders-modal').toggleClass('is-visible');
        jQuery('#submit-new-orders').html(jQuery('#submit-new-orders').data('ohtml'));
        jQuery('#submit-new-orders').attr('disabled', false);
        console.log('Data submitted');
      },
    });
  });
}
