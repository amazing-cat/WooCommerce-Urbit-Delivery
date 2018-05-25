<?php if(!$is_cart): ?>
	<tr class="urb-it-message">
		<th><?php _e('Message to urb-it', self::LANG); ?></th>
		<td>
<?php endif; ?>

            <input id="time_offset" type="text" value="<?= $time_offset->format('%h:%i') ?>" hidden />
			<p id="urb_it_message_field" class="form-row form-row-wide">
				<?php if($is_cart): ?><label for="urb_it_message"><?php _e('Message to urb-it', self::LANG); ?></label><?php endif; ?>
				<input id="urb_it_message" name="urb_it_message" class="input-text" type="text" placeholder="<?php _e('Ex. entry code', self::LANG); ?>" value="<?php echo $message; ?>" />
			</p><!-- #urb_it_message_field -->

    <div class="urbit-privacy-plicy column-right">
        <p>
            By using Urb-it service you accept the
            <a href="https://urb-it.com/terms-of-service">terms</a>
            , agree with the
            <a href="https://urb-it.com/privacy-policy">privacy policy</a>
            and that your entered information is shared with your delivery assistant.
        </p>
    </div>
			
<?php if(!$is_cart): ?>
		</td>
	</tr>
<?php endif; ?>