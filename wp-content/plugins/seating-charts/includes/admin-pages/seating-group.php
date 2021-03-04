<div class="tc-settings-wrap tc-tables-settings" id="tc-seats">

    <div class="tc-settings-wrap  tc-tables-settings" id="tc_seating_group_widget">

        <div class="tc-title-wrap">
            <h4><?php _e('Seating Group', 'tcsc') ?></h4>
        </div><!-- .tc-title-wrap -->

        <div class="tc-options-wrap">

            <div class="tc-input-wrap">
                <label>
                    <?php _e('Title', 'tcsc') ?>
                </label>
                <input type="text" id="tc_seating_group_title" />
            </div><!-- .tc-input-wrap -->

            <div class="tc-input-wrap tc-input-slider tc-seat-rows-slider">

                <label><?php _e('Rows', 'tcsc'); ?></label>

                <div class="tc-number-slider"></div>
                <input type="text" id="tc_seat_add_seats_rows" class="tc-slider-value" />                

            </div><!-- .tc-input-slider -->


            <div class="tc-input-wrap tc-input-slider tc-seat-cols-slider">

                <label><?php _e('Columns', 'tcsc'); ?></label>

                <div class="tc-number-slider"></div>
                <input type="text" id="tc_seat_add_seats_cols" class="tc-slider-value" />                

            </div><!-- .tc-input-slider -->
            
            
            <div class="tc-input-wrap">

                <?php 
                    $tc_seat_types = array('circle', 'square', 'chair', 'car');                
                ?>
                
                <label><?php _e('Icon Type', 'tcsc'); ?></label>

                <div class="tc-seat-choice">
                
                    <ul>

                        <?php foreach($tc_seat_types as $tc_single_seat){ ?>
                        <li>    
                            <input type="radio" id="<?php echo $tc_single_seat; ?>" name="tc_seat_choice" value="<?php echo $tc_single_seat; ?>" class="tc-check-seat-type"/>
                            <label for="<?php echo $tc_single_seat; ?>" class="<?php echo $tc_single_seat; ?>">
                                <?php if($tc_single_seat == 'circle'){ ?>
                                    <span class="tc-circle-icon"></span>
                                <?php } else if($tc_single_seat == 'square') { ?>
                                    <span class="tc-square-icon"></span>
                                <?php } else { ?>
                                    <span class="icon-<?php echo $tc_single_seat; ?> tc-icons-only"></span>
                                <?php }?>
                            </label>
                        </li>
                        <?php } ?>
                    </ul>
                </div>

            </div><!-- .tc-input-slider -->
            

            <div class="tc-clear"></div>

            <div class="tc_seat_add_controls">
                <button class="tc-change-button" type="button" id="tc_add_seats_button"><?php _e('Create', 'tcsc') ?></button>
            </div>

            <div class="tc_seat_edit_controls">
                <button class="tc-edit-button" type="button" id="tc_edit_seats_button"><?php _e('Edit', 'tcsc'); ?></button>
                <button class="tc-cancel-button" id='tc_cancel_seat_button'><?php _e('Cancel', 'tcsc'); ?></button>
            </div>

            <div class="tc-clear"></div>

        </div> <!-- .tc-options-wrap -->

    </div><!-- .tc-settings-wrap -->


</div><!-- .tc-settings-wrap -->