require(['jquery', 'uikit!notify', 'domReady!'], function($, uikit) {

	var formAnswer = $('#js-answer'), 
	id = $('input[name="answer[question_id]"]', formAnswer), 
	message = $('textarea[name="answer[content]"]', formAnswer),
	spinner = $('.js-spinner', formAnswer), 
	dirty = false;

	// form ajax saving
  formAnswer.on('submit', function(e) {

      e.preventDefault();
      e.stopImmediatePropagation();

      spinner.removeClass('uk-hidden');

      $.post(formAnswer.attr('action'), formAnswer.serialize(), function(response) {

          dirty = false;
          uikit.notify(response.message, response.error ? 'danger' : 'success');

          if (response.id) {
          	console.log('test');
              message.val('');
          }

          spinner.addClass('uk-hidden');
      });
  });


});