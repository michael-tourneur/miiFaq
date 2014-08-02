require(['jquery', 'uikit', 'uikit!autocomplete','domReady!'], function($, uikit) {

	var baseUrl = '//' + window.location.host;
	var currentUrl = baseUrl + window.location.pathname;

	var autocomplete = uikit.autocomplete($('#miiFaq-index [name="search"]').parent(), {
    source: function(release) {
      $.getJSON(currentUrl, { filter: {search: this.input.val()} }, function(data) {
        release(data.list);        
      });
    },
    template: '<ul class="uk-nav uk-nav-autocomplete uk-autocomplete-results">{{~items}}<li data-value="{{$item.title}}" data-url="{{$item.url}}" data-id="{{$item.id}}"><a>{{$item.title}}</a></li>{{/items}}</ul>'
  });

	autocomplete.element.on('autocomplete-select', function(e, data){
		if(typeof data.url !== 'undefined' && data.url !== '')
    	window.location.href = baseUrl + data.url;
  });

});