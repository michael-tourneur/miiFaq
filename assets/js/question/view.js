require(['jquery', 'uikit!notify', 'domReady!'], function($, uikit) {

	var formAnswer = $('#js-answer'), filters = $('#js-answers-filter'), answers = $('#js-answers-table'),
	id = $('input[name="answer[question_id]"]', formAnswer), 
	buttonFilterActive = $('.uk-button.active', filters),
	message = $('textarea[name="answer[content]"]', formAnswer),
	spinnerAnswer = $('.js-spinner', formAnswer), 
	spinnerFilters = $('.js-spinner', filters),
	dirty = false;

	// form ajax saving
  formAnswer.on('submit', function(e) {

      e.preventDefault();
      e.stopImmediatePropagation();

      spinnerAnswer.removeClass('uk-hidden');

      $.post(formAnswer.attr('action'), formAnswer.serialize(), function(response) {

          dirty = false;
          uikit.notify(response.message, response.error ? 'danger' : 'success');

          if (response.id) {
              message.val('');
              reloadAnswersTable(buttonFilterActive);
          }

          spinnerAnswer.addClass('uk-hidden');
      });
  });

  // table filters
  filters.on('click', '.uk-button', function (e) {
  		var button = $(this);

      e.preventDefault();
      e.stopImmediatePropagation();

      if(!button.is(buttonFilterActive)) {
  			reloadAnswersTable(button);
      }
  });

  // table reload
  var reloadAnswersTable = function (button) {

  		var index, url, param;

			index = button.attr('href').indexOf('?');
			url = button.attr('href').substring(0, index);
			param = decodeURIComponent(button.attr('href').substring(index+1));

      buttonFilterActive.removeClass('active');
      button.addClass('active');

      spinnerFilters.removeClass('uk-hidden');

      $.get(url, param, function(response) {

          dirty = false;

          if (response.table) {
              answers.html(response.table);
              buttonFilterActive.removeClass('uk-button-danger');
              button.addClass('uk-button-danger');
              buttonFilterActive = button;
          }

          spinnerFilters.addClass('uk-hidden');
      });
  }


});