jQuery(function($) {
	var remember_message = '';

	$(document).on('change', '#urb_it_date', function() {
		set_time_field();
	}).ready(function() {
		set_time_field();
	}).ajaxComplete(function(event, xhr, ajaxOpts) {
		$("#order_confirmation").on("click", function() {
			$(".confirm-dialog").show();
		});
		$("#confirm_yes").on("click", function() {
			$(".confirm-dialog").hide();
		});
		$("#confirm_no").on("click", function() {
			$(".confirm-dialog").hide();
		});

		if(ajaxOpts.url.indexOf('update_order_review') === -1 && ajaxOpts.url.indexOf('urb_it') === -1) return;

		set_time_field();

		if(remember_message) $('#urb_it_message').val(remember_message);
	}).on('change', '#urb_it_message', function() {
		remember_message = $(this).val();
	});

	$(document.body).on('urbit_set_time_field', function() {
		set_time_field();
	});

	function set_time_field() {
		var date = $('#urb_it_date'),
				time = $('#urb_it_time');

		if(!date.length) return;

		var option = date.find(':selected'),
				time_open = option.data('open'),
				time_close = option.data('close'),
                time_first_delivery = option.data('first-delivery'),
                time_last_delivery = option.data('last-delivery'),
				is_today = !!option.data('today');

		// Open all the time
		if(time_open === time_close) {
			$('#urb-it-time').removeAttr('min').removeAttr('max');
		}

		// Specific opening hours
		else {
			// Modify label
			$('#urb_it_time_field .time-display').text('(' + time_open + ' - ' + time_close + ')');

            $('#urb_it_hour').val("");
            $('#urb_it_minute').val("");

			var now_offset = $('#time_offset').val().split(':'),
				now = new Date(),
				local_timezone = now.getTimezoneOffset() / 60,
				date_now = now.getFullYear() + '-' + now.getMonth() + '-' + now.getDay(),
                start_hours_border,
                start_minutes_border,
                end_hours_border,
                end_minutes_border,
                start_border,
                end_border,
				time_now;

			if ((is_today && (parseInt(now.getHours()) >= parseInt(time_open.split(":")[0]))) || !is_today) {
                start_hours_border = parseInt(time_first_delivery.split(":")[0]);
                start_minutes_border = parseInt(time_first_delivery.split(":")[1]) + 15;
            } else {
                start_hours_border = parseInt(now.getHours()) + parseInt(local_timezone) + parseInt(now_offset[0]);
                start_minutes_border = parseInt(now.getMinutes()) + parseInt(now_offset[1]) + 15;
            }

            end_hours_border = parseInt(time_last_delivery.split(":")[0], 10);
			end_minutes_border = parseInt(time_last_delivery.split(":")[1], 10);

            time_now = ('0' + start_hours_border).slice(-2) + ':' + ('0' + start_minutes_border).slice(-2);

            start_border = correct_time(start_hours_border, start_minutes_border);
			end_border = correct_time(end_hours_border, end_minutes_border);

            $('#urb_it_hour option').each(function(){

                //hide
                if($(this).val() < start_border[0]
                || $(this).val() > end_border[0]) $(this).css('display', 'none');

                //show
                if($(this).val() >= start_border[0]
                && $(this).val() <= end_border[0]) $(this).css('display', 'block');

				if (is_today && ($(this).val() < start_border[0])) {
					$(this).css('display', 'none');
				}

                //hide first opening hour if minute opening is later than 45
                if(is_today && start_border[1] > 45 && $(this).val() == start_border[0]) {

                   $(this).css('display', 'none');

			   }

            });

            //update minute field when changing hour field
            $(document.body).on('change', '#urb_it_hour', function(){

                //empty minute field
                $('#urb_it_minute').val("");

                //opening hour

                if($('#urb_it_hour').val() == start_border[0]) {

                    $('#urb_it_minute option').each(function(){

                        //check that minutes are later than opening minute
                        if($(this).val() >= start_border[1]){
                            $(this).css('display', 'block');
                        }else{
                            $(this).css('display', 'none');
                        }

                    });

                //closing hour
                }else if($('#urb_it_hour').val() == end_border[0]) {

                    $('#urb_it_minute option').each(function(){

                        //check that minutes are earlier than closing minute
                        if($(this).val() <= end_border[1]){
                            $(this).css('display', 'block');
                        }else{
                            $(this).css('display', 'none');
                        }

                    });

                //all other hours
                }else{

                    //show rest
                    $('#urb_it_minute option').first().siblings().css('display', 'block');

                }

                //hide placeholder
                $('#urb_it_minute option').first().css('display', 'none');

            });

			// Modify time value if not valid
			if(is_today && time_now > time_close) {
				$('.error').show();
			}
			else {
				$('.error').hide();
			}
		}
	}

	function correct_time(hours_border, minutes_border) {
        if (minutes_border > 59) {
            hours_border++;
            minutes_border -= 60;
        }
        if (minutes_border < 0) {
            hours_border--;
            minutes_border += 60;
        }
        if (hours_border < 0) hours_border += 24;
        if (hours_border > 23) hours_border -= 24;

        return [hours_border, minutes_border];
    }

    //update delivery time field
    $(document.body).on('change', '#urb_it_minute', function(){

        $('#urb_it_time').val($('#urb_it_hour').val() + ":" + $('#urb_it_minute').val());

    });

    //empty hour and minute fields when changing date
    $(document.body).on('change', '#urb_it_date', function(){

        $('#urb_it_hour').val("");
        $('#urb_it_minute').val("");

        $('#urb_it_time').val("");

    });

	// Adjust the lower time each second
	function adjust_time() {
		var option = $('#urb_it_date :selected'),
				is_today = (option.length && option.data('today'));

		if(is_today) {
			var now = new Date(),
				local_timezone = now.getTimezoneOffset() / 60,
				now_offset = $('#time_offset').val().split(':'),
				hour_border = parseInt(now.getHours()) + parseInt(local_timezone) + parseInt(now_offset[0]),
				minutes_border = parseInt(now.getMinutes()) + parseInt(now_offset[1]) + 15,
				time_open = option.data('open'),
				time_close = option.data('close');

			if (minutes_border > 59) {
				hour_border++;
				minutes_border -= 60;
			}
			if (hour_border < 0) hour_border += 24;
			if (hour_border > 23) hour_border -= 24;

			var time_now = ('0' + hour_border).slice(-2) + ':' + ('0' + minutes_border).slice(-2);

			if(time_open < time_now) {
				option.data('open', time_now);
				$('#urb_it_time_field .time-display').text('(' + time_now + ' - ' + time_close + ')');
			}
		}

		setTimeout(adjust_time, 1000);
	}

	adjust_time();

});
