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
      //Successfully posted stories
      $('#posted_message').after('<ul id="success_list"></ul>');
      jQuery.each(data.postedStories, function(headline, linkArray) {
        $('#success_list').append('<li class="bt-story bt-story-success"><h4><a href="' + linkArray.link + '" target="_blank" rel="noopener noreferrer">' + headline + '</a></h4></li>');
      });
      //Stories posted with warnings
      $('#posted_warn_message').after('<ul id="warning_list"></ul>');
      jQuery.each(data.postedWarningStories, function(headline, warningArray) {
        console.log(headline);
        var warnText = '<li class="bt-story bt-story-warning"><h4><a href="' + warningArray.link + '" target="_blank" rel="noopener noreferrer">' + headline + '</a></h4><ul>';

        jQuery.each(warningArray, function(key, warning) {
          if (key != 'link') {
            warnText += '<li>' + warning + '</li>';
          }
        });
        warnText += '</ul></li>';
        $('#warning_list').append(warnText);
      });
      //Stories not posted due to fatal errors
      $('#posted_fatal_message').after('<ul id="fail_list"></ul>');
      jQuery.each(data.failedStories, function(headline, failArray) {
        var failText = '<li class="bt-story bt-story-failed"><h4>' + headline + '</h4><ul>';

        jQuery.each(failArray, function(key, fail) {
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
      api_error(response);
    }).always(function() {
      // e.g. Remove 'loading' class.
      $("#submitButton").prop('type', 'submit');
      $("#submitButton").text('Upload Zip');
    });


  });
});
