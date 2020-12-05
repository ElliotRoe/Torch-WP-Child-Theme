$(function() {
  var plugin_dir=jsVars.pluginUrl+"/";
  function after_form_submitted(data) {
    if (data.fatalError != '') {
      $('#success_message').hide();
      $('#error_message').append('<span></span>');
      $('#error_message span').text(data.fatalError);
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
          warnText += '<li>' + fail + '</li>';
        });
        warnText +='</ul></li>';
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


    var formdata = new FormData(this);
    $.ajax({
      type: "POST",
      url: plugin_dir + 'bt_upload_handler.php',
      data: formdata,
      success: after_form_submitted,
      dataType: 'json',
      processData: false,
      contentType: false,
      cache: false
    });

  });
});
