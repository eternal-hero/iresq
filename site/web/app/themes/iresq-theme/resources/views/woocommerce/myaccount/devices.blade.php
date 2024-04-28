@php
$id = get_current_user_id();
// update_user_meta($id, 'devices', array(
//   '432' => array(
//     'device_name'          => 'ipad',
//     'device_type'          => 'tablet',
//     'device_serial_number' => '432',
//     'device_link'          => ''
//   ),
//   '123' => array(
//     'device_name'          => 'iphone',
//     'device_type'          => 'phone',
//     'device_serial_number' => '123',
//     'device_link'          => ''
//   )
// ));
$devices = '' != get_user_meta(get_current_user_id(), 'devices', true) ? get_user_meta(get_current_user_id(), 'devices', true) : [];
@endphp

@if ( !$devices ) 
  <h4>When you purchase a service from us your devices will be saved here!</h4>
@elseif ($devices)
  <table width="100%" class="devices-table">
    <tr class="devices-table-row">
      <th><h6>Model</h6></th>
      <th><h6>Type</h6></th>
      <th colspan="3"><h6>Serial No.</h6></th>
    </tr>
    @foreach ( $devices as $device )
      <tr class="device-row">
        <td><p>{{ $device['device_name'] }}</p></td>
        <td><p>{{ $device['device_type'] }}</p></td>
        @if( $device['device_serial_number'] )
          <td><p>{{ $device['device_serial_number'] }}</p></td>
        @else
          <td><p>N/A</p></td>
        @endif
        <td class="device-link">
          <a class="device-link-text" href="{{ $device['device_link'] }}">repair</a>
        </td>
        <td class="device-delete">
          <button
            id="device-delete-button"
            data-user_id={{$id}}
            data-remove={{$device['device_serial_number']}}
          >
            delete
          </button>
        </td>
      </tr>
    @endforeach
  </table>
@endif

