<?php

/**
 * Template Name: Statistic
 * Template Post Type: post, page
 *
 * @package WordPress
 * @subpackage Twenty_Twenty
 * @since Twenty Twenty 1.0
 */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />
    <?php wp_head(); ?>
    <title>Thống kê vé</title>
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

        #thupa_event_footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 20px;
            background: #21409A;
        }

        .card {
            width: 300px;
            height: 80px;
        }
    </style>
</head>

<body>
    <main id="site-content" role="main">
        <div class="d-flex justify-content-between bg-warning text-light p-4">
            <img src="" id="thupa_event_logo" />
            <div id="thupa_event_name"></div>
            <div style="width: 100px;"></div>
        </div>
        <div class="container">
            <div class="text-center">
                <h4 class="text-danger mb-1">THỐNG KÊ ĐẠI BIỂU</h4>
            </div>
            <div class="row mb-2 d-flex justify-content-around">
                <div class="d-flex flex-column align-items-center">
                    <h5>Tổng số đại biểu chính thức</h5>
                    <div class="card text-white bg-info">
                        <div class="card-body d-flex justify-content-center align-items-center">
                            <h4 class="card-title m-0" id="thupa_total_quantity"></h4>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column align-items-center">
                    <h5>Đại biểu đã điểm danh</h5>
                    <div class="card text-white bg-success">
                        <div class="card-body d-flex justify-content-center align-items-center">
                            <h4 class="card-title m-0" id="thupa_checkin_quantity"></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-2 d-flex justify-content-around">
                <div class="d-flex flex-column align-items-center">
                    <h5>Đại biểu vắng mặt đã báo cáo</h5>
                    <div class="card text-white bg-danger">
                        <div class="card-body d-flex justify-content-center align-items-center">
                            <h4 class="card-title m-0" id="thupa_absent_quantity"></h4>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column align-items-center">
                    <h5>Đại biểu chưa điểm danh</h5>
                    <div class="card text-white bg-warning">
                        <div class="card-body d-flex justify-content-center align-items-center">
                            <h4 class="card-title m-0" id="thupa_rest_quantity"></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-primary text-light mt-4" id="thupa_event_footer">
            <div class="container d-flex justify-content-between align-items-center">
                <div><b>Địa chỉ: <span id="thupa_event_location"></span></b></div>
                <div><b>Hotline: <span id="thupa_event_hotline"></span></b></div>
            </div>
        </div>
    </main><!-- #site-content -->
    <?php
    wp_enqueue_script('script', get_template_directory_uri() . '/assets/js/js-php-unserialize/php-unserialize.js', '', 1.1, true);
    $data = array('themeUrl' => get_template_directory_uri());
    wp_localize_script('script', 'data', $data);
    ?>
    <script>
        const ROOT_URL = "<?php echo get_site_url(); ?>";
        var searchParams = new URLSearchParams(window.location.search)
        var eventId = searchParams.get('event');
        var ticketTypeId = searchParams.get('ticket_type_id');

        getData();
        setInterval(() => {
            getData();
        }, 5000);

        async function getData() {
            var totalTickets = 0;
            var checkedinTickets = 0;
            var absentTickets = 0;

            var statisticUrl = ROOT_URL + "/wp-json/tickets/v1/statistic-event/" + eventId;
            if (ticketTypeId) statisticUrl = statisticUrl + "?ticket_type_id=" + ticketTypeId;

            await jQuery.ajax({
                url: statisticUrl,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    totalTickets = data.totalTickets;
                    checkedinTickets = data.checkinCount;
                    absentTickets = data.absentCount;
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert(jqXHR.status);
                },
            });

            // get event data
            jQuery.ajax({
                url: ROOT_URL + "/wp-json/wp/v2/tc_events/" + eventId,
                type: "GET",
                dataType: "json",
                success: function(event) {
                    jQuery('#thupa_event_name').html(event.title.rendered);
                    jQuery('#thupa_event_time').html(`${new Date(event.metadata.event_date_time[0]).toLocaleString('vi')} - ${new Date(event.metadata.event_end_date_time[0]).toLocaleString('vi')}`);
                    if (event.metadata.event_location) jQuery('#thupa_event_location').html(event.metadata.event_location[0]);
                    if (event.metadata.event_logo_file_url) jQuery('#thupa_event_logo').attr('src', event.metadata.event_logo_file_url[0]);
                    jQuery('#thupa_event_content').html(event.content.rendered);
                    if (event.metadata.event_hotline) jQuery('#thupa_event_hotline').html(event.metadata.event_hotline[0]);

                    // quantity
                    let restQuantity = totalTickets - checkedinTickets - absentTickets;

                    if (totalTickets <= 0) jQuery('#thupa_total_quantity').html('Không giới hạn');
                    else jQuery('#thupa_total_quantity').html(totalTickets - absentTickets);
                    jQuery('#thupa_checkin_quantity').html(checkedinTickets);
                    jQuery('#thupa_absent_quantity').html(absentTickets);
                    if (restQuantity < 0) jQuery('#thupa_rest_quantity').html('Không giới hạn');
                    else jQuery('#thupa_rest_quantity').html(restQuantity);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert(jqXHR.status);
                },
            })

            console.log(totalTickets, checkedinTickets)
        }
        
    </script>


    <?php wp_footer(); ?>
</body>

</html>