<?php

/**
 * Template Name: Checked-in
 * Template Post Type: post, page
 *
 * @package WordPress
 * @subpackage Twenty_Twenty
 * @since Twenty Twenty 1.0
 */

// get_header();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />
    <?php wp_head(); ?>
    <title>Welcome</title>
    <style>
        body {
            background: #fff;
            font-family: "Times New Roman" !important;
        }

        #thupa_event_logo {
            max-height: 60px;
        }

        #thupa_event_name {
            font-size: 25px;
            text-transform: uppercase;
            font-weight: bold;
            max-width: 70%;
            text-align: center;
            color: red;
            align-items: center;
            display: flex;
        }

        #thupa_attendee_name {
            text-transform: uppercase;
            font-size: 4.5rem;
        }
        
         #thupa_attendee_position {
            font-size: 2.75rem;
        }

        #thupa_ticket_seat {
            color: red
        }

        #thupa_event_footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 20px;
            background: #21409A;
        }
    </style>
</head>

<body>
    <main id="site-content" role="main">

        <div>
            <div class="d-flex justify-content-between bg-warning text-light p-4">
                <img src="" id="thupa_event_logo" />
                <div id="thupa_event_name"></div>
                <div style="width: 100px;"></div>
            </div>

            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-sm-12 text-center">
                        <h6 class="text-danger">CHÀO MỪNG ĐẠI BIỂU</h6>
                        <h2 class="text-dark mt-1" id="thupa_attendee_name"></h2>
                        <h4 class="text-muted mt-1" id="thupa_attendee_position"></h4>
                        <div class="mt-2"></div>
                        <h6>THI GIAN CHECK-IN: <span id="thupa_checked_in_time"></span></h6>
                        <h6 class="mt-2">SỐ GHẾ</h6>
                        <h2 class="mt-1 mb-0" id="thupa_ticket_seat"></h2>
                        <h6 class="mt-4">ĐỀ NGHỊ CÁC ĐẠI BIỂU NGỒI ĐÚNG VỊ TRÍ ĐÃ ĐƯỢC THÔNG BÁO</h6>
                    </div>
                    <div class="col-md-6 col-sm-12 text-center">
                        <!-- <h6 class="text-danger">VỊ TRÍ CHỖ NGỒI</h6> -->
                        <img id="thupa_ticket_position_image" />
                    </div>
                </div>
            </div>

            <div class="bg-primary text-light mt-4" id="thupa_event_footer">
                <div class="container d-flex justify-content-between align-items-center">
                    <div><b>ịa chỉ: <span id="thupa_event_location"></span></b></div>
                    <div><b>Hotline: <span id="thupa_event_hotline"></span></b></div>
                </div>
            </div>
        </div>

    </main><!-- #site-content -->

    <?php
    wp_enqueue_script('script', get_template_directory_uri() . '/assets/js/js-php-unserialize/php-unserialize.js', '', 1.1, true);
    $data = array('themeUrl' => get_template_directory_uri());
    wp_localize_script('script', 'data', $data);
    ?>
    <script>
        var ROOT_URL = "<?php echo get_site_url(); ?>";
        let searchParams = new URLSearchParams(window.location.search)
        let eventId = searchParams.get('event');
        let eventCode = searchParams.get('code');

        loadLatestTicket(eventId, eventCode);

        async function loadLatestTicket(eventId, eventCode) {
            var apiKey = null;

            // get api key of event
            await jQuery.ajax({
                url: ROOT_URL + "/wp-json/wp/v2/tc_api_keys?code=" + eventCode,
                type: "GET",
                // dataType: "json",
                success: function(items) {
                    if (items && items.length) apiKey = items[0];
                    console.log('api_key =========', items[0])
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR, textStatus, errorThrown)
                },
            })

            function getData() {
                console.log(new Date(), 'ping');
                jQuery.ajax({
                    url: ROOT_URL + "/wp-json/wp/v2/tc_tickets_instances?event_id=" + eventId,
                    type: "GET",
                    // dataType: "json",
                    success: function(items) {
                        if (apiKey) {
                            let latestTicket = null;
                            let checkinList = [];
                            let lastCheckedIn = null;

                            items.forEach(item => {
                                if (item.metadata.tc_checkins && item.metadata.tc_checkins.length) {
                                    let checkins = [];
                                    if (item.metadata.tc_checkins[0]) checkins = Object.values(PHPUnserialize.unserialize(item.metadata.tc_checkins[0]));

                                    if (checkinList.length && checkins.length) {
                                        if (checkins[checkins.length - 1].date_checked > checkinList[checkinList.length - 1].date_checked && checkins[checkins.length - 1].api_key_id == apiKey.id) {
                                            if (lastCheckedIn.date_checked < checkins[checkins.length - 1].date_checked) {
                                                latestTicket = item;
                                                checkinList = checkins;
                                                lastCheckedIn = checkins[checkins.length - 1];
                                            }
                                        }
                                    } else {
                                        if (checkins.length) {
                                            if (checkins[checkins.length - 1].api_key_id == apiKey.id) {
                                                latestTicket = item;
                                                checkinList = checkins;
                                                lastCheckedIn = checkins[checkins.length - 1];
                                            }
                                        }
                                    }
                                }
                            })

                            if (lastCheckedIn) {

                                if (apiKey) {
                                    console.log('latest ticket =======', lastCheckedIn)
                                    if (lastCheckedIn.api_key_id == apiKey.id) {
                                        jQuery('#thupa_attendee_name').html(`${latestTicket.metadata.first_name[0]} ${latestTicket.metadata.last_name[0]}`);
                                        jQuery('#thupa_checked_in_time').text(new Date(lastCheckedIn.date_checked * 1000).toLocaleString('vi'));
                                        if (latestTicket.metadata.ticket_owner_position) {
                                            let arr = latestTicket.metadata.ticket_owner_position[0].split("-");
                                            arr = arr.map(str => str.trim());
                                            jQuery('#thupa_attendee_position').html(arr.join(", "));
                                        }
                                        if (latestTicket.metadata.ticket_seat) {
                                            jQuery('#thupa_ticket_seat').html(latestTicket.metadata.ticket_seat[0]);
                                        }
                                        if (latestTicket.metadata.ticket_position_image) {
                                            jQuery('#thupa_ticket_position_image').attr('src', latestTicket.metadata.ticket_position_image[0]);
                                        }
                                    }
                                }
                            }
                        } else {
                            alert("Event code không hợp lệ")
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR, textStatus, errorThrown)
                    },
                })
            }

            // get event data
            jQuery.ajax({
                url: ROOT_URL + "/wp-json/wp/v2/tc_events/" + eventId,
                type: "GET",
                // dataType: "json",
                success: function(event) {
                    jQuery('#thupa_event_name').html(event.title.rendered);
                    jQuery('#thupa_event_time').html(`${new Date(event.metadata.event_date_time[0]).toLocaleString('vi')} - ${new Date(event.metadata.event_end_date_time[0]).toLocaleString('vi')}`);
                    if (event.metadata.event_location) jQuery('#thupa_event_location').html(event.metadata.event_location[0]);
                    if (event.metadata.event_logo_file_url) jQuery('#thupa_event_logo').attr('src', event.metadata.event_logo_file_url[0]);
                    jQuery('#thupa_event_content').html(event.content.rendered);
                    if (event.metadata.event_hotline) jQuery('#thupa_event_hotline').html(event.metadata.event_hotline[0]);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR, textStatus, errorThrown)
                },
            })
            // get checked-in tickets
            getData();
            // auto refresh tickets list every 30s
            setInterval(() => {
                getData();
            }, 3000);
        }
    </script>
    <?php wp_footer(); ?>
</body>

</html>