jQuery(document).ready(function($) {

  function ajax_error(data) {
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
        warnText +='</ul></li>';
        $('#posted_message ul').append(warnText);
      });

      jQuery.each(data.failedStories, function(headline, failArray) {
        var failText = '<li>' + headline + '<ul>';

        jQuery.each(failArray, function(key, fail) {
          failText += '<li>' + fail + '</li>';
        });
        failText +='</ul></li>';
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

    $form = $(this);
    //show some response on the button
    $('button[type="submit"]', $form).each(function() {
      $btn = $(this);
      $btn.prop('type', 'button');
      $btn.prop('orig_label', $btn.text());
      $btn.text('Sending ...');
    });


    var formData = new FormData(this);

    const workPlease = [...formData.entries()];

    console.log(workPlease);
    console.log(formData.has('tmp_name'));

/*
    jQuery.post(ajax_object.ajax_url, data, function(response) {
    		alert('Got this from the server: ' + response);
    	});
*/

    jQuery.ajax({
      type: "POST",
      url: ajax_object.ajax_url,
      data: {
        formData: formData,
        action: 'bt_upload_handler'
      },
      success: after_form_submitted,
      error: ajax_error,
      dataType: 'json',
      processData: false,
      contentType: false,
      cache: false
    });


  });
});
