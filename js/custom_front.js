(function($) {
  var date = $('#date').val();
  $('.wilson_ads_counter').dsCountDown({   
    theme: 'flat',
    endDate: new Date(date),
    titleDays: 'DAYS', // Set the title of days
    titleHours: 'HOURS', // Set the title of hours
    titleMinutes: 'MIN', // Set the title of minutes
    titleSeconds: 'SEC', // Set the title of seconds
  });
})( jQuery );