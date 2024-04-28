<?php
/**
 * 100% custom template and route. This will not be overriden by WooCommerce.
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();
?>

<div id="my-bulk-orders">
  <div class=" tw-flex tw-flex-wrap">
    <h3 class="tw-mt-0 tw-flex-grow tw-mb-0">My Bulk Orders</h3>
    <div class="tw-mt-3 md:tw-mt-0">
      <button class="solid-red iresq-button new-orders-modal-toggle" id="new-orders-toggle">Add New Orders</button>
    </div>
  </div>
  <section>
    <h4 class="tw-mb-4">My Account Details</h4>
    <div class="tw-flex tw-flex-wrap tw--mx-5">
      <div class="tw-w-full md:tw-w-1/2 tw-px-5 tw-mb-5">
        <p>
          <strong>Account Name</strong><br>
          {{ $firstInvoice["fieldData"]["CLIENT::Acct Name"] }}
        </p>
        <p>
          <strong>Address</strong><br>
          {{ $billInfo["BILLADDRESS::Address 1"] }}<br>
          @if($billInfo["BILLADDRESS::Address 2"])
          {{ $billInfo["BILLADDRESS::Address 2"] }}<br>
          @endif
          {{ $billInfo["BILLADDRESS::City"] }}, {{ $billInfo["BILLADDRESS::ST"] }} {{ $billInfo["BILLADDRESS::Zip"] }}
        </p>
      </div>
      @if($firstInvoice["fieldData"]["ACTUALSHIPADDRESS::t.ShipTo.Company.Name"] || $firstInvoice["fieldData"]["ACTUALSHIPADDRESS::Email"] || $firstInvoice["fieldData"]["ACTUALSHIPADDRESS::t.ShipToPhone"])
      <div class="tw-w-full md:tw-w-1/2 tw-px-5 tw-mb-5">
        <p>
          <strong>Point of Contact</strong><br>
          @if($firstInvoice["fieldData"]["ACTUALSHIPADDRESS::t.ShipTo.Company.Name"])
          {{ $firstInvoice["fieldData"]["ACTUALSHIPADDRESS::t.ShipTo.Company.Name"] }}<br>
          @endif
          @if($firstInvoice["fieldData"]["ACTUALSHIPADDRESS::Email"])
          {{ $firstInvoice["fieldData"]["ACTUALSHIPADDRESS::Email"] }}<br>
          @endif
          @if($firstInvoice["fieldData"]["ACTUALSHIPADDRESS::t.ShipToPhone"])
          {{ $firstInvoice["fieldData"]["ACTUALSHIPADDRESS::t.ShipToPhone"]}}<br>
          @endif
        </p>
      </div>
      @endif
    </div>
  </section>

  <section class="tw-pb-5">
    <h5 class="tw-mb-4">Filter Orders</h5>
    <div class="tw-flex tw-flex-wrap tw--mx-3">
      <div class="tw-px-3 tw-mb-4 tw-w-full md:tw-w-1/4">
        <label class="tw-font-bold">By PO#</label><br>
        <input type="text" class="iresq-text-input tw-mt-2 tw-w-full" placeholder="Enter your PO number here" id="po-filter" />
      </div>
      <div class="tw-px-3 tw-mb-4 tw-w-full md:tw-w-1/4">
        <label class="tw-font-bold">By Serial#</label><br>
        <input type="text" class="iresq-text-input tw-mt-2 tw-w-full" placeholder="Enter your serial number here" id="serial-filter" />
      </div>
      <div class="tw-px-3 tw-mb-4 tw-w-full md:tw-w-1/4">
        <label class="tw-font-bold">By Date Range</label><br>
        <select class="iresq-text-input tw-mt-2 tw-w-full" id="date-filter">
          <option value="" selected>Any</option>
          <option value="30">Past 30 Days</option>
          <option value="60">Past 60 Days</option>
          <option value="90">Past 90 Days</option>
          <option value="120">Past 120 Days</option>
        </select>
      </div>
      <div class="tw-px-3 tw-mb-4 tw-w-full md:tw-w-1/4">
        <label class="tw-font-bold">By Status</label><br>
        <select class="iresq-text-input tw-mt-2 tw-w-full" id="status-filter">
          <option value="">Any</option>
          <option value="POSTED">Processed</option>
        </select>
      </div>
    </div>
    <div class="tw-flex tw-justify-end">
      <button class="solid-red iresq-button" id="filter-results" data-button-spinner="Loading...">Filter Results</button>
    </div>
  </section>

  <section class="tw-pb-5">
    <h4 class="tw-mb-5">Repairs In Progress</h4>
    <table id="repairing" class="stripe hover" style="width:100%; padding-top: 1em;  padding-bottom: 1em;">
      <thead>
        <tr>
          <th></th>
          <th>PO#</th>
          <th>Invoice Date</th>
          <th>Model</th>
          <th>Serial#</th>
          <th>Cost</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
      </tbody>

    </table>
  </section>

  <section>
    <h4 class="tw-mb-5">Completed Repairs</h4>
    <table id="processed" class="stripe hover" style="width:100%; padding-top: 1em;  padding-bottom: 1em;">
      <thead>
        <tr>
          <th></th>
          <th>PO#</th>
          <th>Invoice Date</th>
          <th>Model</th>
          <th>Serial#</th>
          <th>Cost</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
      </tbody>

    </table>
  </section>
</div>

<!--Datatables -->
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
<script>
  var ajaxUrl = '{{ admin_url('admin-ajax.php') }}';
  jQuery(document).ready(function() {

    var table = jQuery('#repairing').DataTable({
        responsive: true,
        serverSide: true,
        pagingType: "simple_numbers",
        bInfo: false,
        'processing': true,
        "ordering": false,
        searching: false,
        "dom": '<"top"i>rt<"bottom"flp><"clear">',
        ajax: {
          url: ajaxUrl + '?action=getmybulkinprogress',
          "data": function ( d ) {
            return jQuery.extend( {}, d, {
              "po_filter": jQuery("#po-filter").val().toLowerCase(),
              "serial_filter": jQuery("#serial-filter").val().toLowerCase(),
              "date_filter": jQuery("#date-filter").val().toLowerCase(),
              "status_filter": jQuery("#status-filter").val().toLowerCase(),
            } );
          }
        },
        'language':{
          "loadingRecords": "&nbsp;",
          "processing": "Loading..."
        },
        columns: [
          {
            "className":      'details-control',
            "orderable":      false,
            "data":           null,
            "defaultContent": '<i class="fas fa-plus-circle"></i>'
          },
          { data: 'fieldData.PO No', className: "tw-text-left" },
          { data: 'fieldData.Invoice Date', className: "tw-text-left" },
          { data: 'fieldData.Model', className: "tw-text-left" },
          { data: 'fieldData.Serial No', className: "tw-text-left" },
          { data: 'fieldData.cInvoiceTotal', className: "tw-text-right" },
          { data: 'fieldData.Major Status', className: "tw-text-left" },
        ],
        columnDefs: [
          {
            "render": function (data, type, row) {
              return '$'+data;
            },
            "targets": 5
          }
        ]
      })
      .columns.adjust()
      .responsive.recalc();

    var tableTwo = jQuery('#processed').DataTable({
        responsive: true,
        serverSide: true,
        'processing': true,
        "ordering": false,
        pagingType: "simple_numbers",
        bInfo: false,
        'language':{
          "loadingRecords": "&nbsp;",
          "processing": "Loading..."
        },
        "dom": '<"top"i>rt<"bottom"flp><"clear">',
        searching: false,
        ajax: {
          url: ajaxUrl + '?action=getmybulkprocessed',
          "data": function ( d ) {
            return jQuery.extend( {}, d, {
              "po_filter": jQuery("#po-filter").val().toLowerCase(),
              "serial_filter": jQuery("#serial-filter").val().toLowerCase(),
              "date_filter": jQuery("#date-filter").val().toLowerCase(),
              "status_filter": jQuery("#status-filter").val().toLowerCase(),
            } );
          }
        },
        columns: [
          {
            "className":      'details-control',
            "orderable":      false,
            "data":           null,
            "defaultContent": '<i class="fas fa-plus-circle"></i>'
          },
          { data: 'fieldData.Client PO#', className: "tw-text-left" },
          { data: 'fieldData.Invoice Date', className: "tw-text-left" },
          { data: 'fieldData.Model', className: "tw-text-left" },
          { data: 'fieldData.Serial No', className: "tw-text-left" },
          { data: 'fieldData.cInvoiceTotal', className: "tw-text-right" },
          { data: 'fieldData.Major Status', className: "tw-text-left" },
        ],
        columnDefs: [
          {
            "render": function (data, type, row) {
              return '$'+data;
            },
            "targets": 5
          }
        ]
      })
      .columns.adjust()
      .responsive.recalc();

      function resetButton() {
        jQuery("#filter-results").html(jQuery("#filter-results").data("ohtml"));
        jQuery("#filter-results").attr("disabled", false);
      }

      jQuery('#processed tbody, #repairing tbody').on('click', 'td.details-control', function () {
        var tr = jQuery(this).closest('tr');
        var row = table.row( tr );

        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            // Open this row
            row.child( childRowFormat(row.data()) ).show();
            tr.addClass('shown');
        }
      });

      /* Formatting function for row details - modify as you need */
      function childRowFormat( d ) {
        // `d` is the original data object for the row
        var returnText = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">';
        returnText += "<tr>";
        if(d.fieldData["Client Notes"]) {
          returnText += "<td>Provided Notes:</td>";
          returnText += '<td>'+d.fieldData["Client Notes"]+'</td>';
        }
        returnText += "</tr>";
        returnText += '</table>';
        return returnText;
      }

      jQuery("#filter-results").click(function() {
        var $this = jQuery(this);
        if($this.attr("disabled")) {
          return;
        }
        $this.data("ohtml", $this.html());
        var nhtml = "<i class='fas fa-spinner fa-spin tw-mr-2'></i> " + this.dataset.buttonSpinner;
        $this.html(nhtml);
        $this.attr("disabled", true);

        table.ajax.reload(resetButton);
        tableTwo.ajax.reload(resetButton);
      });
  } );

</script>

{{-- Begin Modal Code --}}
<div class="iresq-modal" id="new-orders-modal">
  <div class="modal-overlay new-orders-modal-toggle"></div>
  <div class="modal-wrapper modal-transition">
    <div class="modal-header">
      <button class="modal-close new-orders-modal-toggle">
        <i class="fas fa-times tw-text-3xl"></i>
      </button>
      <h3 class="modal-heading">Create New Orders</h3>
    </div>

    <div class="modal-body">
      <div class="modal-content">
        <div class="tw-flex tw-flex-wrap tw--mx-5">
          <div class="tw-w-1/2 tw-px-5">
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Impedit eum delectus, libero, accusantium dolores inventore obcaecati placeat cum sapiente vel laboriosam similique totam id ducimus aperiam, ratione fuga blanditiis maiores.</p>
          </div>
          <div class="tw-w-1/2 tw-px-5">
            <div class="tw-mb-2">
              <label class="tw-font-bold" for="bulk-bus-name">School/Business Name</label><br>
              <input type="text" class="iresq-text-input-sm tw-mt-2 tw-w-full" id="bulk-bus-name" value="{{ $firstInvoice["fieldData"]["ACTUALSHIPADDRESS::t.ShipTo.Company.Name"] }}" />
            </div>
            <div class="tw-pb-5">
              <label class="tw-font-bold" for="bulk-street1">Address</label><br>
              <input type="text" class="iresq-text-input-sm tw-mt-2 tw-w-full" id="bulk-street1" value="{{ $firstInvoice["fieldData"]["ACTUALSHIPADDRESS::Address 1"] }}" />
              <input type="text" class="iresq-text-input-sm tw-mt-2 tw-w-full" id="bulk-street2" value="{{ $firstInvoice["fieldData"]["ACTUALSHIPADDRESS::Address 2"] }}" />
              <div class="tw-flex tw--mx-2">
                <div class="tw-px-2 tw-w-5/12">
                  <input type="text" class="iresq-text-input-sm tw-mt-2 tw-w-full" id="bulk-city" value="{{ $firstInvoice["fieldData"]["ACTUALSHIPADDRESS::City"] }}" />
                </div>
                <div class="tw-px-2 tw-w-3/12">
                  <input type="text" class="iresq-text-input-sm tw-mt-2 tw-w-full" id="bulk-state" value="{{ $firstInvoice["fieldData"]["ACTUALSHIPADDRESS::ST"] }}" />
                </div>
                <div class="tw-px-2 tw-w-4/12">
                  <input type="text" class="iresq-text-input-sm tw-mt-2 tw-w-full" id="bulk-zip" value="{{ $firstInvoice["fieldData"]["ACTUALSHIPADDRESS::Zip"] }}" />
                </div>
              </div>
            </div>
          </div>
        </div>
        <div id="newBulkOrdersTable"></div>
        <div class="tw-flex tw-justify-end tw-pt-4">
          <button class="solid-red iresq-button" id="submit-new-orders"  data-button-spinner="Importing...">Submit New Orders</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  jQuery('.new-orders-modal-toggle').on('click', function(e) {
    e.preventDefault();
    jQuery('#new-orders-modal').toggleClass('is-visible');
  });
</script>

{{-- End Modal Code --}}
