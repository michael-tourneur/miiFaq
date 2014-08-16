require(['jquery', 'uikit!notify', 'gravatar', 'domReady!'], function($, uikit, gravatar) {

	var formAnswer = $('#js-answer'), filters = $('#js-answers-filter'), answers = $('#js-answers-table'),
	id = $('input[name="answer[question_id]"]', formAnswer), 
	buttonFilterActive = $('.uk-button.active', filters),
	message = $('textarea[name="answer[content]"]', formAnswer),
	spinnerAnswer = $('.js-spinner', formAnswer), 
	spinnerFilters = $('.js-spinner', filters),
  answer = $('.miifaq-answer'),
  avatar = $('.js-avatar', answer),
  reloadAnswersTable,
  reloadGravatar,
	dirty = false;

  // table reload
  reloadAnswersTable = function (button) {

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

          reloadGravatar();
      });
  }

  reloadGravatar = function () {
    $.each(avatar, function() {
      $(this).html('<img src="' + gravatar.url($(this).data('email'), {s: 25, d: 'mm', r: 'g'}) + '" class="uk-border-circle" height="25" width="25">');
    });
  }

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

  answer.on('click', '.js-answer-vote', function (e) {
    var button, index, url, param;

    e.preventDefault();
    e.stopImmediatePropagation();

    button = $(this);
    index = button.attr('href').indexOf('?');
    url = button.attr('href').substring(0, index);
    param = decodeURIComponent(button.attr('href').substring(index+1));

    e.preventDefault();
    e.stopImmediatePropagation();

    $.get(url, param, function(response) {

      dirty = false;
      uikit.notify(response.message, response.error ? 'danger' : 'success');

      if(response.vote) {
        button.parents('.miifaq-answer').find('.vote').html(response.vote);
      }

    });
  });

  reloadGravatar();


});