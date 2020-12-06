jQuery(document).ready(function($) {

  function api_error(data) {
    console.log(data);
  }

  function after_form_submitted(data) {
    if (data.fatalError != '') {
      $('#success_message').hide();
      $('#error_message').append('<span id="specific_err"></span>');
      $('#specific_err').text(data.fatalError);
      $('#error_message').show();
    } else {
      $('#posted_message').append('<ul></ul>');

      jQuery.each(data.postedStories, function(key, headline) {
        $('#posted_message ul').append('<li>' + val + '</li>');
      });
      jQuery.each(data.postedWarningStories, function(headline, warningArray) {
        var warnText = '<li>' + headline + '<ul>';

        jQuery.each(warningArray, function(key, warning) {
          warnText += '<li>' + warning + '</li>';
        });
        warnText += '</ul></li>';
        $('#posted_message ul').append(warnText);
      });

      jQuery.each(data.failedStories, function(headline, failArray) {
        var failText = '<li>' + headline + '<ul>';

        jQuery.each(failArray, function(key, fail) {
          failText += '<li>' + fail + '</li>';
        });
        failText += '</ul></li>';
        $('#posted_message ul').append(failText);
      });

      $('#success_message').show();
      $('#error_message').hide();

      //reverse the response on the button
      $('button[type="button"]', $form).each(function() {
        $btn = $(this);
        label = $btn.prop('orig_label');
        if (label) {
          $btn.prop('type', 'submit');
          $btn.text(label);
          $btn.prop('orig_label', '');
        }
      });

    } //else
  }

  $('#bt-upload-form').submit(function(e) {
    e.preventDefault();

    console.log("Submtted");

    $form = $(this);
    //show some response on the button
    $("#submitButton").text('Sending ...');
    $("#submitButton").prop('type', 'button');


    var formData = new FormData(this);

    // Endpoint from wpApiSetting variable passed from wp-api.
    var endpoint = wpApiSettings.root + 'bt/v2/upload/';
    $.ajax({
      url: endpoint,
      method: 'POST',
      beforeSend: function(xhr) {
        // Set nonce here
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      },
      data: formData,
      dataType: 'json',
      processData: false,
      contentType: false,
      cache: false
    }).done(function(response) {
      after_form_submitted(response);
    }).fail(function(response) {
      api_error(response);
    }).always(function() {
      // e.g. Remove 'loading' class.
      $("#submitButton").prop('type', 'submit');
      $("#submitButton").text('Upload Zip');
    });


  });
});
