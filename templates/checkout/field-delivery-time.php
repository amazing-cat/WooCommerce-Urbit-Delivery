<?php if(!$is_cart): ?>
	<tr class="urb-it-delivery-time">
		<th><?php _e('Delivery time', self::LANG); ?></th>
		<td>
<?php endif; ?>

			<input id="time_offset" type="text" value="<?= $time_offset->format('%h:%i') ?>" hidden />

			<?php if(!$hide_date_field): ?>
				<p id="urb_it_date_field" class="form-row form-row-wide">
					<label for="urb_it_date"><?php _e('Day', self::LANG); ?></label>
					<select id="urb_it_date" name="urb_it_date">
						<option value="Day" disabled selected="selected">Day</option>
						<?php foreach($days as $day): ?>
							<?php
                                $is_today = ($day->open->format('Y-m-d') === $now->format('Y-m-d'));
                                if ($is_today && $day->last_delivery < $now) continue;
							?>
							<option value="<?= $day->open->format('Y-m-d') ?>"
                                    data-first-delivery="<?= $day->first_delivery->format('H:i') ?>"
                                    data-last-delivery="<?= $day->last_delivery->format('H:i') ?>"
                                    data-open="<?= $day->open->format('H:i') ?>"
                                    data-close="<?= $day->close->format('H:i') ?>"
                                <?php if($is_today): ?>
                                    data-today="true"
                                <?php endif; ?>>
                                <?= ucfirst($is_today ? __('today', self::LANG) :
                                    date_i18n('l', $day->open->getTimestamp())) ?>
                            </option>
						<?php endforeach; ?>
					</select>
				</p>
			<?php endif; ?>

			<?php if(!$hide_time_field): ?>
				<p id="urb_it_time_field" class="form-row form-row-wide">
					<label for="urb_it_time"><?php _e('Time', self::LANG); ?></label>
					<label class="time-display"></label>

                    <select id="urb_it_hour" name="urb_it_hour">

                        <option value="" disabled selected>HH</option>

                        <?php foreach(range(0, 23, 1) as $hour): ?>

                            <option value="<?php echo $hour; ?>"><?php echo $hour; ?></option>

                        <?php endforeach; ?>

                    </select>

                    <select id="urb_it_minute" name="urb_it_minute">

                        <option value="" disabled selected>mm</option>

                        <?php foreach(range(0, 45, 15) as $minute): ?>

                            <?php if($minute == 0) $minute = '00'; ?>

                            <option value="<?php echo $minute; ?>"><?php echo $minute; ?></option>

                        <?php endforeach; ?>

                    </select>
				</p>
			<?php endif; ?>

<?php if(!$is_cart): ?>
		</td>
	</tr>
<?php endif; ?>
