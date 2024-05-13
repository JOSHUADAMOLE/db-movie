<?php

include 'admin/db_connect.php';
$ts = $conn->query("SELECT * FROM theater_settings where theater_id=".$_GET['id']);
$data = array();
while($row=$ts->fetch_assoc()){
    $data[] = $row;
    $seat_group[$row['id']] = $row['seat_group'];
    $seat_count[$row['id']] = $row['seat_count'];
}

$mov = $conn->query("SELECT * FROM movies where id =".$_GET['mid'])->fetch_array();
$dur = explode('.', $mov['duration']);
$dur[1] = isset($dur[1]) ? $dur[1] : 0;
$hr = sprintf("%'.02d\n",$dur[0]);
$min = isset($dur[1]) ? (60 * ('.'.$dur[1])) : '0';
$min = sprintf("%'.02d\n",$min);
$duration = $hr.' : '.$min;

?>

<div class="row">
    <div class="form-group col-md-4">
        <label for="" class="control-label">Choose Seat Group</label>
        <select name="seat_group" id="seat_group" class="custom-select default-browser">
            <option value=""></option>
            <?php foreach($seat_group as $k => $v): ?>
                <option value="<?php echo $k ?>" data-count="<?php echo $seat_count[$k] ?>"><?php echo $v ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="display-count" class="col-md-5 mt-4 pt-2"></div>        
</div>

<div class="row">
    <div class="form-group col-md-2">
        <label for="" class="control-label">Qty</label>
        <input type="number" name="qty" id="qty" class="form-control" min="0" required="">
    </div>
    <div class="form-group col-md-4">
        <label for="" class="control-label">Date</label>
        <select name="date" id="date" class="custom-select browser-default">
            <?php 
                $start_date = strtotime($mov['date_showing']);
                $end_date = strtotime($mov['end_date']);
                $current_date = strtotime(date('Y-m-d'));
                
                for ($i = 0; $i <= ($end_date - $start_date) / (60 * 60 * 24); $i++) {
                    $date = date('Y-m-d', strtotime("+$i days", $start_date));
                    if ($current_date >= $start_date + $i * 24 * 3600) {
                        echo "<option value='$date'>" . date('M d, Y', strtotime("+$i days", $start_date)) . "</option>";
                    }
                }
            ?>
        </select>
    </div>
    <div class="form-group col-md-4">
        <label for="" class="control-label">Time</label>
        <select name="time" id="time" class="custom-select browser-default">
            <?php 
            $i = 1;
            $start = '2020-01-01 09:00';
            $time='';
            $dur[1] = isset($dur[1]) ? $dur[1] : 0;
            while ( $i < 10) {
                if(empty($time)){
                    echo '<option value="'.date('h:i A',strtotime($start)).'">'.date('h:i A',strtotime($start)).'</option>';
                    $time = date('h:i A',strtotime($start));
                } else {
                    $time = empty($time) ? $start : $time;
                    if(date('Hi',strtotime($time)) < '2100'){
                        echo '<option value="'.date('h:i A',strtotime($time.' +'.$dur[0].' hours +'.$dur[1].' minutes')).'">'.date('h:i A',strtotime('+'.$dur[0].' hours +'.$dur[1].' minutes'.$time)).'</option>';
                        $time = date('Y-m-d H:i',strtotime('+'.$dur[0].' hours +'.$dur[1].' minutes'.$time));
                    }
                }
                $i++;
            }
            ?>
        </select>
    </div>
</div>

<!-- Seat Selection -->
<div class="row">
    <div class="col-md-12">
        <h5>Select Seats:</h5>
        <div id="seat-map">
            <div class="screen">Screen</div>
            <div class="seats">
                <?php 
                // Assuming the total number of seats is fixed, you can generate seat elements dynamically based on seat_count
                for ($i = 1; $i <= $seat_count[$k]; $i++) {
                    echo '<div class="seat" data-seat="'.$i.'">Seat ' . $i . '</div>';
                }
                ?>
            </div>
        </div>
        <div id="selected-seats">
            <h6>Selected Seats:</h6>
            <ul></ul>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var selectedSeats = [];

        // Function to toggle seat selection
        function toggleSeatSelection(seat) {
            var seatNumber = seat.data('seat');
            if (selectedSeats.includes(seatNumber)) {
                // Seat already selected, remove it
                selectedSeats = selectedSeats.filter(function(value) {
                    return value != seatNumber;
                });
                seat.removeClass('selected');
            } else {
                // Seat not selected, add it
                selectedSeats.push(seatNumber);
                seat.addClass('selected');
            }
            updateSelectedSeatsUI();
        }

        // Function to update selected seats UI
        function updateSelectedSeatsUI() {
            var selectedSeatsList = $('#selected-seats ul');
            selectedSeatsList.empty();
            selectedSeats.forEach(function(seatNumber) {
                selectedSeatsList.append('<li>Seat ' + seatNumber + '</li>');
            });
        }

        // Click event handler for seat selection
        $('#seat-map .seat').click(function() {
            var seat = $(this);
            if (!seat.hasClass('selected') && !seat.hasClass('unavailable')) {
                toggleSeatSelection(seat);
            }
        });
    });
</script>

<style>
    #seat-map {
        text-align: center;
    }

    .screen {
        font-weight: bold;
        margin-bottom: 10px;
        padding: 10px 0;
        display: inline-block;
    }

    .seats {
        display: flex;
        justify-content: center;
        align-items: flex-end;
        flex-wrap: wrap;
    }

    .seat {
        display: inline-block;
        width: 80px; /* Adjust as needed */
        height: 50px;
        background-color: #ccc;
        margin: 5px;
        cursor: pointer;
        text-align: center;
        line-height: 50px;
    }

    .seat.selected {
        background-color: #ff0000; /* Red */
        color: #fff;
    }

    .seat.unavailable {
        background-color: #999; /* Gray */
        cursor: not-allowed;
    }
</style>



