var init_global_pre = jQuery.Callbacks();
var init_global_post = jQuery.Callbacks();

var Page = {

	init: function(){
		Page.initTrainingSelect();
	},

	initTrainingSelect: function(){
		$('#trainingplan-select').bind('change', function(){
			const baseref = window.location.origin;
			const urlParams = new URLSearchParams(window.location.search);
			urlParams.set('trainingplan', this.value);
			window.location.href = baseref + '/?' + urlParams.toString();
		})
	}
}

init_global_pre.add(function(){Page.init()});

var Paging = {

	init: function(){
		if(!$('#training-wrapper').length) return;
		var weeks = $('.training');
		$('#training-wrapper').css({width: (weeks.length * weeks[0].offsetWidth)+'px'});
		$('.paging').bind('click', Paging.page);
		Paging.doPaging();
	},

	page: function(e){
		if($(this).hasClass('paging-back')){
			if(window.currentweek>1)
				window.currentweek--;
		}else{
			if(window.currentweek<window.totalweeks)
				window.currentweek++;
		}
		Paging.doPaging();
		this.blur();
	},

	doPaging: function(){
		var weeks = $('.training');
		var offset = weeks[0].offsetWidth * (window.currentweek-1) * -1;
		$('#training-wrapper').css({marginLeft:offset+'px'});

		if(window.currentweek==1)
			$('.paging-back').addClass('hidden');
		else
			$('.paging-back').removeClass('hidden');

		if(window.currentweek==window.totalweeks)
			$('.paging-next').addClass('hidden');
		else
			$('.paging-next').removeClass('hidden');
	},

};

init_global_pre.add(function(){Paging.init()});

var Counter = {
	hours: 0,
	minutes: 0,
	seconds: 0,
	timeout: null,
	toCountdown: null,
	
	init: function(timeout){
		Counter.hours = $('#counter #hours').text();
		Counter.minutes = $('#counter #minutes').text();
		Counter.seconds = $('#counter #seconds').text();
		Counter.timeout = timeout;
		Counter.toCountdown = setInterval(function(){Counter.doCountdown()}, Counter.timeout);	
	},
	
	doCountdown: function(){
		if(Counter.seconds > 0)
			Counter.seconds--;
		else{
			Counter.seconds = 59;
			if(Counter.minutes > 0)
				Counter.minutes--;
			else{
				Counter.minutes = 59;
				Counter.hours--;
			}
		}
		if(Counter.hours == 0 && Counter.minutes == 0 && Counter.seconds == 0){
			$('#counter').text("IT'S HERE!");
			clearInterval(Counter.toCountdown);
			return;
		}
		
		$('#counter #hours').text(Counter.hours);
		$('#counter #minutes').text(Counter.minutes);
		$('#counter #seconds').text(Counter.seconds);
		if(Counter.hours==1)
			$('#hours-plural').text('');
		else
			$('#hours-plural').text('S');
		if(Counter.minutes==1)
			$('#minutes-plural').text('');
		else
			$('#minutes-plural').text('S');
		if(Counter.seconds==1)
			$('#seconds-plural').text('');
		else
			$('#seconds-plural').text('S');
//		console.log(Counter.minutes + ' '+Counter.seconds);
	}
};

if(typeof timeout != 'undefined' && timeout != null){
	init_global_pre.add(function(){Counter.init(timeout)});
}

var Info = {
	init: function(){
		$('#schedule .trainingday .activity').bind('click', Info.show);
		type = $('#schedule .trainingday.today .activity').attr('rel');
		Info.show(type);
	},
	
	show: function(){
		if(typeof arguments[0] == 'string'){
			type = arguments[0];
		}else{
			$this = $(this);
			type = $this.attr('rel');
			$('.activity').removeClass('active');
			$this.addClass('active');
		}
		$('#infotexts .info').addClass('nodisplay');
		$('#infotexts #'+type+'-info').removeClass('nodisplay');		
	}
};

init_global_pre.add(Info.init);

// fire init_global_pre callback functions when DOM is loaded
jQuery(document).ready(function(){
    init_global_pre.fire();
});

// only call init_global_post callback functions if something must be initialized *after* images are loaded
window.onload = function(){init_global_post.fire();}
