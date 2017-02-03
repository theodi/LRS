define(function(require) {

	var Adapt = require('coreJS/adapt');

});

var theme = "ODI";
var interval; 
var click_bind = false;
var platform = "web";

$(document).ready(function() {
	setTimeout(function() {updateLanguageSwitcher(); },1000);
	setTimeout(function() {$(".dropdown dt a").show();$('#country-select').show();},2000);
	interval = setInterval(function() { checkState(); },5000);
});

var moduleId = "";
$.getJSON("course/config.json",function(data) {
        moduleId = data._moduleId;
});

function addListeners() {
	if (!click_bind) {
		$('.save-section-outer').click(function() {
			$('#cloud-status').slideToggle();
		});
		click_bind = true;
	}
}

function emailSave(email) {
	localStorage.setItem("email",email);
	$('#save-section').fadeOut( function() {
    		var sl = document.getElementById('save-section');
		var ss = document.getElementById('cloud-status-text');
		$(sl).html("");
		$(sl).addClass('saving');
		var toClass = "cloud_saving";
		$(sl).css('background-image','url(adapt/css/assets/' + toClass + '.gif)');
		$(ss).html(config["_phrases"][toClass]);
		var ssi = document.getElementById('cloud-status-img');
		$(ssi).attr('src','adapt/css/assets/' + toClass + '.gif');
		$(sl).fadeIn();
		addListeners();
		checkState();
		interval = setInterval(function() { checkState(); },5000);
	});
}

function showSave() {
	var email=prompt("Please enter your email...");
	emailSave(email);
}

function checkState() {
	var sessionEmail = localStorage.getItem("email");
	var sessionID = localStorage.getItem("_id");
	var lastSave = localStorage.getItem(moduleId + "_lastSave");

	if (!sessionEmail && sessionID) {
		$('#save-section').html("<button onClick='showSave();' class='slbutton' id='saveSession'>Save progress</button>");
		$('#save-section').fadeIn();
		clearInterval(interval);
		click_bind = false;
		$('.save-section-outer').unbind('click');
        	$("#country-select").removeClass('status-shown');
		$("#country-select").addClass('save-shown');
	} else if (sessionID) {
		if (!lastSave) { lastSave = "Unknown"; }
		$('#save-status').html("Module ID: " + moduleId + "<br/>Session ID: " + sessionID + "<br/>Last Save: " + lastSave);
		$('#save-section').addClass('saving');
		$("#country-select").removeClass('save-shown');
		$("#country-select").addClass('status-shown');
		addListeners();
	} else {
    		var sl = document.getElementById('save-section');
		var ss = document.getElementById('cloud-status-text');
		$(sl).addClass('saving');
		var toClass = "cloud_failed";
		$(sl).css('background-image','url(adapt/css/assets/' + toClass + '.gif)');
		$(ss).html(config["_phrases"][toClass]);
		var ssi = document.getElementById('cloud-status-img');
		$(ssi).attr('src','adapt/css/assets/' + toClass + '.gif');
		$('#save-section').fadeIn();
		$("#country-select").removeClass('save-shown');
		$("#country-select").addClass('status-shown');
		addListeners();
	}	
}

function updateLanguageSwitcher() {
	createDropDown();

        var $dropTrigger = $(".dropdown dt a");
        var $languageList = $(".dropdown dd ul");

	$(".dropdown dt a").click(function() {
		$dropTrigger.addClass("active");
		$languageList.slideDown(200);
	});
        // open and close list when button is clicked
        $dropTrigger.toggle(function() {
                $languageList.slideDown(200);
                $dropTrigger.addClass("active");
        }, function() {
                $languageList.slideUp(200);
                $(this).removeAttr("class");
        });

        // close list when anywhere else on the screen is clicked
        $(document).bind('click', function(e) {
                var $clicked = $(e.target);
                if (! $clicked.parents().hasClass("dropdown"))
                       $languageList.slideUp(200);
                       $dropTrigger.removeAttr("class");
       });

        // when a language is clicked, make the selection and then hide the list
        $(".dropdown dd ul li a").click(function() {
                var clickedValue = $(this).parent().attr("class");
                var clickedTitle = $(this).find("em").html();
                $("#target dt").removeClass().addClass(clickedValue);
                $("#target dt em").html(clickedTitle);
                $languageList.hide();
                $dropTrigger.removeAttr("class");
		if (lang != clickedValue) {
			current = window.location.href;
			newLocation = current.replace("/" + lang + "/","/" + clickedValue + "/");
			window.location.href = newLocation;
		}
        });
}

function createDropDown(){	
	var $form = $("div#country-select form");
	$form.hide();
	var source = $("#country-options");
	source.removeAttr("autocomplete");
	var selected = source.find('[title="'+lang+'"]');
	var options = $("option", source);
	$("#country-select").append('<dl id="target" class="dropdown"></dl>')
		$("#target").append('<dt class="' + selected.val() + '"><a href="#" style="display: inline;"><span class="flag"></span><em>' + selected.text() + '</em></a></dt>')
		$("#target").append('<dd><ul></ul></dd>')
		options.each(function(){
				$("#target dd ul").append('<li class="' + $(this).val() + '"><a href="' + $(this).attr("title") + '"><span class="flag"></span><em>' + $(this).text() + '</em></a></li>');
				});
}
