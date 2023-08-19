<?php
require_once('connect.php');
include('header.php');
include('navbar.php');
?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php
// Your database query to get the stock list
$query = "
    SELECT 
        ai.article_name,
        h.type,
        h.time,
        h.changed_value
    FROM article_info ai
    LEFT JOIN history h ON ai.article_id = h.article_id
    WHERE h.type IN ('入庫', '出庫')
    ORDER BY ai.category_id, ai.article_order
";

$stmt = $con->prepare($query);
//$stmt = $con->prepare("SELECT article_name, stock FROM article_info ORDER BY category_id,article_order");
$stmt->execute();
$result = $stmt->get_result();


// Prepare the data for the Chart.js
$data = [];
while ($row = $result->fetch_assoc()) {
    $date = new DateTime($row['time']);
    $yearMonth = $date->format('Y-m'); // Get year and month as "yyyy-mm"

    if (!isset($data[$row['article_name']])) {
        $data[$row['article_name']] = [];
    }

    if (!isset($data[$row['article_name']][$row['type']])) {
        $data[$row['article_name']][$row['type']] = [];
    }

    if (!isset($data[$row['article_name']][$row['type']][$yearMonth])) {
        $data[$row['article_name']][$row['type']][$yearMonth] = 0;
    }

    $data[$row['article_name']][$row['type']][$yearMonth] += $row['changed_value'];
}

$labels = json_encode($labels);
$data = json_encode($data);
?>

<div class="col-10 bg-light content">
<table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">Article Name</th>
                <th scope="col">Select</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $stmt = $con->prepare("SELECT article_id, article_name FROM article_info ORDER BY category_id,article_order");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['article_name']}</td>";
            echo "<td><input type='checkbox' class='article-checkbox' data-name='{$row['article_name']}' data-id='{$row['article_id']}'></td>";
            echo "</tr>";
        }
        $result->free();
        ?>
        </tbody>
    </table>
    <canvas id="stockChart"></canvas>
</div>




<script>
var data = <?php echo $data; ?>;  // Getting data from PHP
var checkboxes = document.getElementsByClassName('article-checkbox');
var ctx = document.getElementById('stockChart').getContext('2d');
var datasets = [];

for (var item in data) {
    for (var operation in data[item]) {
        var labels = [];
        var datasetData = [];

        for (var date in data[item][operation]) {
            labels.push(date);
            datasetData.push(data[item][operation][date]);
        }

        var dataset = {
            label: item + " " + operation,
            data: datasetData,
            borderColor: operation == '入庫' ? 'rgba(75, 192, 192, 1)' : 'rgba(255, 99, 132, 1)',
            fill: false
        };

        datasets.push(dataset);
    }
}

// Create a variable to hold the chart instance
var myChart;

function drawChart(datasetsToPlot) {
    if (myChart) {
        myChart.destroy();
    }
    myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasetsToPlot
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Stock History',
                fontSize: 24, // Increase the font size of the title
            },
            tooltips: {
                mode: 'index',
                intersect: false,
                bodyFontSize: 24, // Increase the font size of the tooltips body
                titleFontSize: 24, // Increase the font size of the tooltips title
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                xAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Month',
                        fontSize: 24, // Increase the font size of x-axis scale label
                    },
                    ticks: {
                        fontSize: 24, // Increase the font size of x-axis ticks
                    }
                }],
                yAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Value',
                        fontSize: 24, // Increase the font size of y-axis scale label
                    },
                    ticks: {
                        fontSize: 24, // Increase the font size of y-axis ticks
                    }
                }]
            }
        }
    });
}

function redrawChart() {
    var selectedDatasets = [];

    for (var i = 0; i < checkboxes.length; i++) {
        var checkbox = checkboxes[i];
        if (checkbox.checked) {
            var articleName = checkbox.getAttribute('data-name');
            for (var j = 0; j < datasets.length; j++) {
                var dataset = datasets[j];
                if (dataset.label.startsWith(articleName + " ")) {
                    selectedDatasets.push(dataset);
                }
            }
        }
    }
    drawChart(selectedDatasets);
}

for (var i = 0; i < checkboxes.length; i++) {
    checkboxes[i].addEventListener('change', redrawChart);
}

// Initial chart draw
redrawChart();
</script>




<?php include('footer.php'); ?>