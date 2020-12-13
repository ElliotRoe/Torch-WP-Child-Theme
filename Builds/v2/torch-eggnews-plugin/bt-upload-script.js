jQuery(document).ready(function($) {

  function api_error(data) {
    console.log(data);
  }

  function display_fatal(error_text) {
    $('#success_message').hide();
    $('#error_message').append('<span id="specific_err"></span>');
    $('#specific_err').text(error_text);
    $('#error_message').show();
  }

  function after_form_submitted(data) {
    if (data.fatalError != '') {
      display_fatal(data.fatalError);
    } else {
      //Successfully posted stories
      $('#posted_message').after('<ul id="success_list"></ul>');
      jQuery.each(data.postedStories, function(i, infoArray) {
        $('#success_list').append('<li class="bt-story bt-story-success"><h4>Headline: <a href="' + infoArray.link + '" target="_blank" rel="noopener noreferrer">' + infoArray.headline + '</a><br>Filename: ' + infoArray.filename + '</h4></li>');
      });
      //Stories posted with warnings
      $('#posted_warn_message').after('<ul id="warning_list"></ul>');
      jQuery.each(data.postedWarningStories, function(i, infoArray) {
        console.log(infoArray.headline);
        var warnText = '<li class="bt-story bt-story-warning">Headline: <h4><a href="' + infoArray.link + '" target="_blank" rel="noopener noreferrer">' + infoArray.headline + '</a><br>Filename: ' + infoArray.filename + '</h4><ul>';

        jQuery.each(infoArray.warnings, function(j, warning) {
          warnText += '<li>' + warning + '</li>';
        });
        warnText += '</ul></li>';
        $('#warning_list').append(warnText);
      });
      //Stories not posted due to fatal errors
      $('#posted_fatal_message').after('<ul id="fail_list"></ul>');
      jQuery.each(data.failedStories, function(i, infoArray) {
        var failText = '<li class="bt-story bt-story-failed"><h4>' + infoArray.filename + '</h4><ul>';

        jQuery.each(infoArray.fails, function(j, fail) {
          failText += '<li>' + fail + '</li>';
        });
        failText += '</ul></li>';
        $('#fail_list').append(failText);
      });


      $('#success_message').show();
      $('#error_message').hide();

    }
  }

  $('#bt-upload-form').submit(function(e) {
    e.preventDefault();

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
      console.log(response);
    }).fail(function(response) {
      display_fatal(response);
    }).always(function() {
      // e.g. Remove 'loading' class.
      $("#submitButton").prop('type', 'submit');
      $("#submitButton").text('Upload Zip');
    });


  });
});
