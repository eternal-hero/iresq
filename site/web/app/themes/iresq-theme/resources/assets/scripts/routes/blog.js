export default {
  init() {
    // JavaScript to be fired on all pages
  },
  finalize() {
    var repairStorage = sessionStorage.getItem('repair');
    var deviceStorage = sessionStorage.getItem('device');
    var contentStorage = sessionStorage.getItem('content');

    $('#expert-advice-repair-field option[value="'+repairStorage+'"]').prop('selected', 'selected');
    $('#expert-advice-device-field option[value="'+deviceStorage+'"]').prop('selected', 'selected');
    $('#expert-advice-content-field option[value="'+contentStorage+'"]').prop('selected', 'selected');

    setTimeout(function(){ 
      sessionStorage.removeItem('repair');
      sessionStorage.removeItem('device');
      sessionStorage.removeItem('content');
     }, 1000);

  $('#blog-filter').on('submit', function() {
      sessionStorage.setItem('repair', $('#expert-advice-repair-field').val());
      sessionStorage.setItem('device', $('#expert-advice-device-field').val());
      sessionStorage.setItem('content', $('#expert-advice-content-field').val());
    });
  },
};
