jQuery(function($) {
	var remember_message = '',
        start_border,
        end_border,
        date,
        option,
        time_open,
        time_close,
        time_first_delivery,
        time_last_delivery,
        is_today,
        now_offset;

	$(document).ajaxComplete(function(event, xhr, ajaxOpts) {
        now_offset = $('#time_offset').val().split(':');
        adjust_time();

		/*$("#order_confirmation").on("click", function() {
			$(".confirm-dialog").show();
		});
		$("#confirm_yes").on("click", function() {
			$(".confirm-dialog").hide();
		});
		$("#confirm_no").on("click", function() {
			$(".confirm-dialog").hide();
		});*/

		if(ajaxOpts.url.indexOf('update_order_review') === -1 && ajaxOpts.url.indexOf('urb_it') === -1) return;

		if(remember_message) $('#urb_it_message').val(remember_message);
	}).on('change', '#urb_it_message', function() {
		remember_message = $(this).val();
	});

	function update_days() {
        $('#urb_it_time_field .time-display').text('Today is not available anymore');
        option.remove();
        date.trigger("change");
	}

	function update_hours() {
        $('#urb_it_hour').val("");
        $('#urb_it_minute').val("");

        $('#urb_it_hour option').each(function(){
            //hide
            if($(this).val() < start_border[0] || $(this).val() > end_border[0])
            	$(this).css('display', 'none');

            //show
            if($(this).val() >= start_border[0] && $(this).val() <= end_border[0])
            	$(this).css('display', 'block');

            if (is_today && ($(this).val() < start_border[0]))
                $(this).css('display', 'none');

            //hide last available hour if minute is later than 45
            if(is_today && start_border[1] > 45 && parseInt($(this).val()) === start_border[0])
                $(this).css('display', 'none');
        });
	}

	function update_minutes() {
        //empty minute field
        $('#urb_it_minute').val("");
        var current_hour = parseInt($('#urb_it_hour').val());

        if(current_hour === start_border[0])
            $('#urb_it_minute option').each(function(){

                //check that minutes are later than opening minute
                if($(this).val() >= start_border[1])
                    $(this).css('display', 'block');
                else
                    $(this).css('display', 'none');
            });

            //closing hour
        else if(current_hour === end_border[0])

            $('#urb_it_minute option').each(function(){

                //check that minutes are earlier than closing minute
                if($(this).val() <= end_border[1]){
                    $(this).css('display', 'block');
                }else{
                    $(this).css('display', 'none');
                }
            });

            //all other hours
        else
            //show rest
            $('#urb_it_minute option').first().siblings().css('display', 'block');

        //hide placeholder
        $('#urb_it_minute option').first().css('display', 'none');
	}

	function correct_time(hours_border, minutes_border) {
		while (minutes_border > 59) {
            hours_border++;
            minutes_border -= 60;
        }

        if (hours_border > 23) {
            hours_border -= 24;
            update_days();
        }

        return [hours_border, minutes_border];
    }

    function borders_set(now, local_timezone) {
        var start_hours_border,
            start_minutes_border,
            end_hours_border,
            end_minutes_border;

        if ((is_today && (parseInt(now.getHours()) < parseInt(time_open.split(":")[0]))) || !is_today) {
            start_hours_border = parseInt(time_first_delivery.split(":")[0]);
            start_minutes_border = parseInt(time_first_delivery.split(":")[1]) + 15;
        } else {
            start_hours_border = parseInt(now.getHours()) + parseInt(local_timezone) + parseInt(now_offset[0]);
            start_minutes_border = parseInt(now.getMinutes()) + parseInt(now_offset[1]) + 15;
        }

        end_hours_border = parseInt(time_last_delivery.split(":")[0], 10);
        end_minutes_border = parseInt(time_last_delivery.split(":")[1], 10);

        start_border = correct_time(start_hours_border, start_minutes_border);
        end_border = correct_time(end_hours_border, end_minutes_border);
	}

	// Adjust the lower time each second
	function adjust_time() {

		if(is_today) {
			var now = new Date(),
				local_timezone = now.getTimezoneOffset() / 60;

            borders_set(now, local_timezone);

			var time_first = ('0' + start_border[0]).slice(-2) + ':' + ('0' + start_border[1]).slice(-2);

			if(time_last_delivery > time_first) {
				option.data('open', time_first);
				$('#urb_it_time_field .time-display').text('(' + time_first + ' - ' + time_last_delivery + ')');
			} else
                update_days();
		}

		setTimeout(adjust_time, 1000);
	}

    $(document.body).on('change', '#urb_it_date', function() {
    	var now = new Date();
        date = $('#urb_it_date').first();
        option = date.find(':selected');
        is_today = (option.length && option.data('today'));
        time_open = option.data('open');
        time_close = option.data('close');
        time_first_delivery = option.data('first-delivery');
        time_last_delivery = option.data('last-delivery');
        borders_set(now, now.getTimezoneOffset() / 60);
        update_hours();
        if (!is_today)
            $('#urb_it_time_field .time-display').text('(' + time_first_delivery + ' - ' + time_last_delivery + ')');
	});

    $(document.body).on('change', '#urb_it_hour', function() {
        update_minutes();
    });
});
