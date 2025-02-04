
<?php include 'includes/head.php'; ?>

<?php include 'includes/navbar.php'; ?>

<?php
// Query per ottenere i dati dalla tabella
$query = "SELECT sur_id, description, red_surv, durata, goal, complete, end_field FROM t_panel_control WHERE stato=0 ORDER BY stato, giorni_rimanenti ASC, id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ottenere la data di oggi
$oggi = new DateTime();

// Query per il numero totale di utenti iscritti (active = 1, confirm = 1)
$queryTotalUsers = "SELECT COUNT(*) AS total_users FROM t_user_info WHERE active = 1 AND confirm = 1";
$stmt = $pdo->prepare($queryTotalUsers);
$stmt->execute();
$totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

// Query per il numero di utenti attivi (actions > 1)
$queryActiveUsers = "SELECT COUNT(*) AS active_users FROM t_user_info WHERE active = 1 AND confirm = 1 AND actions > 1";
$stmt = $pdo->prepare($queryActiveUsers);
$stmt->execute();
$activeUsers = $stmt->fetch(PDO::FETCH_ASSOC)['active_users'];

// Calcolo percentuale attivi (evita divisione per zero)
$activePercentage = ($totalUsers > 0) ? round(($activeUsers / $totalUsers) * 100, 2) : 0;

// Inizializzazione variabili per evitare errori
$totalMen = $totalWomen = $activeMen = $activeWomen = 0;

// Query per contare gli uomini e le donne iscritti (active = 1, confirm = 1)
$queryGenderDistribution = "
    SELECT gender, COUNT(*) AS total 
    FROM t_user_info 
    WHERE active = 1 AND confirm = 1 
    GROUP BY gender";
$stmt = $pdo->prepare($queryGenderDistribution);
$stmt->execute();
$genderResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($genderResults as $row) {
    if ($row['gender'] == 1) {
        $totalMen = $row['total'];
    } elseif ($row['gender'] == 2) {
        $totalWomen = $row['total'];
    }
}

// Query per contare uomini e donne attivi (actions > 1)
$queryActiveGender = "
    SELECT gender, COUNT(*) AS active_total 
    FROM t_user_info 
    WHERE active = 1 AND confirm = 1 AND actions > 1 
    GROUP BY gender";
$stmt = $pdo->prepare($queryActiveGender);
$stmt->execute();
$activeGenderResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($activeGenderResults as $row) {
    if ($row['gender'] == 1) {
        $activeMen = $row['active_total'];
    } elseif ($row['gender'] == 2) {
        $activeWomen = $row['active_total'];
    }
}

// Calcolo percentuali di utenti attivi
$totalUsers = $totalMen + $totalWomen;
$activeMenPercentage = ($totalMen > 0) ? round(($activeMen / $totalMen) * 100, 2) : 0;
$activeWomenPercentage = ($totalWomen > 0) ? round(($activeWomen / $totalWomen) * 100, 2) : 0;

// Inizializzazione variabili per evitare errori
$ageGroups = [
    "Under 18" => 0,
    "18-24" => 0,
    "25-34" => 0,
    "35-44" => 0,
    "45-54" => 0,
    "55-65" => 0,
    "Over 65" => 0
];

// Query per contare gli utenti in ogni fascia d'età (active = 1)
$queryAgeDistribution = "
    SELECT 
        CASE 
            WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(birth_date, '%Y-%m-%d'), CURDATE()) < 18 THEN 'Under 18'
            WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(birth_date, '%Y-%m-%d'), CURDATE()) BETWEEN 18 AND 24 THEN '18-24'
            WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(birth_date, '%Y-%m-%d'), CURDATE()) BETWEEN 25 AND 34 THEN '25-34'
            WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(birth_date, '%Y-%m-%d'), CURDATE()) BETWEEN 35 AND 44 THEN '35-44'
            WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(birth_date, '%Y-%m-%d'), CURDATE()) BETWEEN 45 AND 54 THEN '45-54'
            WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(birth_date, '%Y-%m-%d'), CURDATE()) BETWEEN 55 AND 65 THEN '55-65'
            ELSE 'Over 65'
        END AS age_group,
        COUNT(*) AS total
    FROM t_user_info 
    WHERE active = 1
    GROUP BY age_group";
$stmt = $pdo->prepare($queryAgeDistribution);
$stmt->execute();
$ageResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($ageResults as $row) {
    $ageGroups[$row['age_group']] = $row['total'];
}

// Inizializzazione delle variabili per evitare errori
$areaGroups = [
    "Nord Ovest" => 0,
    "Nord Est" => 0,
    "Centro" => 0,
    "Sud e Isole" => 0
];

// Query per contare gli utenti attivi in ogni area
$queryAreaDistribution = "
    SELECT area, COUNT(*) AS total 
    FROM t_user_info 
    WHERE active = 1 
    GROUP BY area";
$stmt = $pdo->prepare($queryAreaDistribution);
$stmt->execute();
$areaResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($areaResults as $row) {
    switch ($row['area']) {
        case 1:
            $areaGroups["Nord Ovest"] = $row['total'];
            break;
        case 2:
            $areaGroups["Nord Est"] = $row['total'];
            break;
        case 3:
            $areaGroups["Centro"] = $row['total'];
            break;
        case 4:
            $areaGroups["Sud e Isole"] = $row['total'];
            break;
    }
}


// Inizializzazione array con 12 mesi a 0
$monthlyRegistrations = array_fill(1, 12, 0);
$monthlyActiveRegistrations = array_fill(1, 12, 0);

// Ottieni l'anno corrente
$currentYear = date("Y");

// Query per contare i registrati mese per mese
$queryRegistrations = "
    SELECT MONTH(STR_TO_DATE(reg_date, '%Y-%m-%d %H:%i:%s')) AS month, COUNT(*) AS total 
    FROM t_user_info 
    WHERE YEAR(STR_TO_DATE(reg_date, '%Y-%m-%d %H:%i:%s')) = :currentYear
    GROUP BY month";
$stmt = $pdo->prepare($queryRegistrations);
$stmt->execute(['currentYear' => $currentYear]);
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($registrations as $row) {
    $monthlyRegistrations[$row['month']] = $row['total'];
}

// Query per contare i registrati attivi mese per mese
$queryActiveRegistrations = "
    SELECT MONTH(STR_TO_DATE(reg_date, '%Y-%m-%d %H:%i:%s')) AS month, COUNT(*) AS total 
    FROM t_user_info 
    WHERE YEAR(STR_TO_DATE(reg_date, '%Y-%m-%d %H:%i:%s')) = :currentYear AND actions > 0
    GROUP BY month";
$stmt = $pdo->prepare($queryActiveRegistrations);
$stmt->execute(['currentYear' => $currentYear]);
$activeRegistrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($activeRegistrations as $row) {
    $monthlyActiveRegistrations[$row['month']] = $row['total'];
}

// Inizializzazione array con gli ultimi 5 anni
$years = range($currentYear - 4, $currentYear);
$activeUsersPerYear = array_fill_keys($years, 0);

// Query per contare gli utenti attivi per anno
$queryActiveUsers = "
    SELECT COUNT(DISTINCT story.user_id) AS total, YEAR(event_date) AS year
    FROM t_user_history AS story
    WHERE story.event_type NOT IN ('subscribe', 'unsubscribe')
    AND YEAR(event_date) BETWEEN :startYear AND :currentYear
    GROUP BY year";
$stmt = $pdo->prepare($queryActiveUsers);
$stmt->execute(['startYear' => $currentYear - 4, 'currentYear' => $currentYear]);
$activeResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($activeResults as $row) {
    $activeUsersPerYear[$row['year']] = $row['total'];
}

?>

<main class="content">
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-12 col-lg-12 col-xxl-12 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <h4 class="card-title mb-0">Progetti in corso</h4>
                </div>
                <table class="table table-hover table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Ricerca</th>
                            <th class="d-none d-xl-table-cell">Info</th>
                            <th>IR</th>
                            <th>LOI</th>
                            <th>Andamento</th>
                            <th>Scadenza</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['sur_id']); ?></td>
                            <td class="d-none d-xl-table-cell"><?php echo htmlspecialchars($row['description']); ?></td>
                            <td>
                                <?php
                                $ir = floatval($row['red_surv']); // Converte il valore in numero
                                $irClass = "text-success"; // Verde di default
                                if ($ir < 30) {
                                    $irClass = "text-danger"; // Rosso se sotto 30%
                                } elseif ($ir < 65) {
                                    $irClass = "text-warning"; // Giallo tra 30% e 60%
                                }
                                ?>
                                <span class="fa-solid fa-computer-mouse"></span>
                                &nbsp;
                                <b><span class="<?php echo $irClass; ?>"><?php echo htmlspecialchars($row['red_surv']); ?>%</span></b>
                            </td>
                            <td>
                            <span class="fa-solid fa-business-time"></span>
                            &nbsp;
                                <?php echo htmlspecialchars($row['durata']); ?>min.</td>
                            <td >
                            <?php
                                // Calcolo andamento (percentuale tra goal e complete, max 100%) e arrotondamento senza decimali
                                $andamento = 0;
                                if (!empty($row['goal']) && is_numeric($row['goal']) && $row['goal'] > 0) {
                                    $andamento = min(100, round(($row['complete'] / $row['goal']) * 100)); // Arrotondato senza decimali
                                }
                                ?>
                            <canvas style="text-align: center" id="chart-<?php echo $row['sur_id']; ?>" width="120" height="45"></canvas>
                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    var ctx = document.getElementById("chart-<?php echo htmlspecialchars($row['sur_id']); ?>").getContext("2d");
                                    
                                    // Assicuriamoci che andamento sia sempre un numero valido
                                    var andamento = <?php echo $andamento; ?>; // Arrotondato senza decimali
                                    
                                    new Chart(ctx, {
                                        type: "doughnut",
                                        data: {
                                            labels: ["Goal %", "Missing %"],
                                            datasets: [{
                                                data: [andamento, 100 - andamento],
                                                backgroundColor: ["#4CAF50", "#E0E0E0"],
                                                borderWidth: 1
                                            }]
                                        },
                                        options: {
                                            responsive: false,
                                            cutoutPercentage: 70, // Riduce la dimensione del centro per una ciambella più sottile
                                            maintainAspectRatio: false,
                                            legend: {
                                                display: false
                                            },
                                            tooltips: {
                                                enabled: true
                                            }
                                        }
                                    });
                                });
                            </script>
                        </td>
                        <td>
                        <?php
                        if (!empty($row['end_field'])) {
                            $endFieldDate = new DateTime($row['end_field']);
                            $differenza = $oggi->diff($endFieldDate)->format("%r%a");

                            if ($differenza == 0) {
                                echo "<span class='badge bg-primary'>Oggi</span>";
                            } elseif ($differenza < 0) {
                                echo "<span class='badge bg-danger'>Scaduto</span>";
                            } else {
                                echo "<span class='badge bg-success'>" . $differenza . " giorni</span>";
                            }
                        } else {
                            echo "<span class='badge bg-secondary'>N/A</span>";
                        }
                        ?>
                    </td>

                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class="row">
    <div class="col-xl-6 col-xxl-5 d-flex">
        <div class="w-100">
            <div class="row">
                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col mt-0">
                                    <h5 class="card-title">Total User Panel</h5>
                                </div>
                                <div class="col-auto">
                                    <div class="stat text-primary">
                                        <i class="align-middle" data-feather="user-check"></i>
                                    </div>
                                </div>
                            </div>
                            <h1 class="mt-1 mb-3"><?php echo number_format($totalUsers); ?></h1>
                            <div class="mb-0">
                                <span class="text-success"> <i class="mdi mdi-arrow-bottom-right"></i> <?php echo $activePercentage; ?>% </span>
                                <span class="text-muted">Utenti attivi</span>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col mt-0">
                                    <h5 class="card-title">Distribuzione Età</h5>
                                </div>
                                <div class="col-auto">
                                    <div class="stat text-primary">
                                        <i class="align-middle" data-feather="bar-chart-2"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="chart-container" style="position: relative; width: 100%; height: 300px;">
                                <canvas id="ageChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col mt-0">
                                    <h5 class="card-title">Genere</h5>
                                </div>
                                <div class="col-auto">
                                    <div class="stat text-primary">
                                        <i class="align-middle" data-feather="users"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="chart-container" style="position: relative; width: 100px; height: 100px; margin: auto;">
                                <canvas id="genderChart"></canvas>
                            </div>

                            <div class="mt-3 text-center">
                                <span class="text-primary"><i class="fas fa-male"></i> Uomini Attivi: <?php echo $activeMenPercentage; ?>%</span>
                                <br>
                                <span class="text-danger"><i class="fas fa-female"></i> Donne Attive: <?php echo $activeWomenPercentage; ?>%</span>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col mt-0">
                                    <h5 class="card-title">Distribuzione per Area</h5>
                                </div>
                                <div class="col-auto">
                                    <div class="stat text-primary">
                                        <i class="align-middle" data-feather="map-pin"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="chart-container" style="position: relative; width: 100%; height: 217px;">
                            <canvas id="areaChart"></canvas>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <div class="col-xl-6 col-xxl-7">
        <div class="card flex-fill w-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Utenti registrati - <?php echo $currentYear ?></h5>
            </div>
            <div class="card-body py-3">
                 <div class="chart-container" style="position: relative; width: 100%; height: 212px;">
                    <canvas id="registrationsChart"></canvas>
                </div>
                <hr> <!-- Separatore per una divisione chiara -->
                <h5 class="card-title mb-0">Attività Utenti ultimi 5 anni</h5>
                <div class="chart-container" style="position: relative; width: 100%; height: 212px;">
                    <canvas id="userActivityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>





</div>



				
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById("genderChart").getContext("2d");
        new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: ["Uomini", "Donne"],
                datasets: [{
                    data: [<?php echo $totalMen; ?>, <?php echo $totalWomen; ?>],
                    backgroundColor: ["#0693e3", "#E91E63"],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                cutoutPercentage: 80,
                maintainAspectRatio: false,
                legend: {
                           display: false
                           },
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: (tooltipItem) => tooltipItem[0].label,
                            label: (tooltipItem) => tooltipItem.raw + " utenti"
                        }
                    }
                }
            }
        });
    });

//age

document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById("ageChart").getContext("2d");
        new Chart(ctx, {
            type: "bar",
            data: {
                labels: ["Under 18", "18-24", "25-34", "35-44", "45-54", "55-65", "Over 65"],
                datasets: [{
                    label: "Numero Utenti",
                    data: [<?php echo implode(',', $ageGroups); ?>],
                    backgroundColor: [
                        "rgba(255, 99, 132, 0.7)", // Rosso
                        "rgba(54, 162, 235, 0.7)", // Blu
                        "rgba(255, 206, 86, 0.7)", // Giallo
                        "rgba(75, 192, 192, 0.7)", // Verde
                        "rgba(255, 159, 64, 0.7)", // Arancione
                        "rgba(153, 102, 255, 0.7)", // Viola
                        "rgba(201, 203, 207, 0.7)"  // Grigio
                    ],
                    borderRadius: 10, // Angoli arrotondati
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                        display: false // Nasconde la legenda
                    },
                indexAxis: 'y', // Grafico orizzontale
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            display: false // Rimuove linee di sfondo
                        },
                        ticks: {
                            font: {
                                size: 13,
                                weight: 'bold'
                            }
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 13
                            }
                        }
                    }
                },
                plugins: {

                    tooltip: {
                        backgroundColor: "rgba(0, 0, 0, 0.8)",
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: (tooltipItem) => tooltipItem.raw + " utenti"
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'right',
                        formatter: (value) => value + " utenti",
                        font: {
                            weight: 'bold',
                            size: 11
                        }
                    }
                },
                animation: {
                    duration: 500,
                    easing: "easeInOutBounce"
                }
            }
        });
    });

//area

document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById("areaChart").getContext("2d");

        var areaData = [<?php echo implode(',', $areaGroups); ?>];
        var totalUsers = areaData.reduce((a, b) => a + b, 0);

        new Chart(ctx, {
            type: "pie",
            data: {
                labels: ["Nord Ovest", "Nord Est", "Centro", "Sud e Isole"],
                datasets: [{
                    data: areaData,
                    backgroundColor: [
                        "rgba(255, 99, 132, 0.8)", // Rosso
                        "rgba(54, 162, 235, 0.8)", // Blu
                        "rgba(255, 206, 86, 0.8)", // Giallo
                        "rgba(75, 192, 192, 0.8)"  // Verde
                    ],
                    borderColor: "#fff",
                    borderWidth: 2,
                    hoverOffset: 10 // Espansione al passaggio del mouse
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                        display: false // Nasconde la legenda
                    },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 14
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                var value = tooltipItem.raw;
                                var percentage = ((value / totalUsers) * 100).toFixed(2) + "%";
                                return tooltipItem.label + ": " + value + " utenti (" + percentage + ")";
                            }
                        }
                    },
                    datalabels: {
                        color: "#fff",
                        font: {
                            weight: 'bold',
                            size: 14
                        },
                        formatter: function(value, ctx) {
                            let percentage = ((value / totalUsers) * 100).toFixed(1) + "%";
                            return percentage;
                        }
                    }
                }
            }
        });
    });

//registrations + userActivities

document.addEventListener("DOMContentLoaded", function() {
    var ctx1 = document.getElementById("registrationsChart").getContext("2d");
    var ctx2 = document.getElementById("userActivityChart").getContext("2d");

    // Gradient per il primo grafico (registrazioni)
    var gradient1 = ctx1.createLinearGradient(0, 0, 0, 200);
    gradient1.addColorStop(0, "rgba(54, 162, 235, 0.4)");
    gradient1.addColorStop(1, "rgba(54, 162, 235, 0)");

    var gradient2 = ctx1.createLinearGradient(0, 0, 0, 200);
    gradient2.addColorStop(0, "rgba(255, 99, 132, 0.4)");
    gradient2.addColorStop(1, "rgba(255, 99, 132, 0)");

    // Primo grafico: Registrazioni mese per mese
    new Chart(ctx1, {
        type: "line",
        data: {
            labels: ["Gen", "Feb", "Mar", "Apr", "Mag", "Giu", "Lug", "Ago", "Set", "Ott", "Nov", "Dic"],
            datasets: [{
                label: "Registrati",
                data: [<?php echo implode(',', $monthlyRegistrations); ?>],
                borderColor: "#36A2EB",
                backgroundColor: gradient1,
                fill: true,
                borderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointBackgroundColor: "#36A2EB",
                pointBorderColor: "#fff",
                tension: 0.4
            },
            {
                label: "Registrati Attivi",
                data: [<?php echo implode(',', $monthlyActiveRegistrations); ?>],
                borderColor: "#FF6384",
                backgroundColor: gradient2,
                fill: true,
                borderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointBackgroundColor: "#FF6384",
                pointBorderColor: "#fff",
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 14, weight: "bold" } }
                },
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [5, 5], color: "rgba(0, 0, 0, 0.1)" },
                    ticks: { stepSize: 10, font: { size: 14 } }
                }
            },
            plugins: {
                legend: { position: "top", labels: { font: { size: 14 } } },
                tooltip: {
                    backgroundColor: "rgba(0, 0, 0, 0.8)",
                    bodyFont: { size: 14 },
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.dataset.label + ": " + tooltipItem.raw + " utenti";
                        }
                    }
                }
            },
            animation: { duration: 1500, easing: "easeInOutQuart" }
        }
    });

    // Gradient per il secondo grafico (attività utenti)
    var gradient3 = ctx2.createLinearGradient(0, 0, 0, 200);
    gradient3.addColorStop(0, "rgba(75, 192, 192, 0.4)");
    gradient3.addColorStop(1, "rgba(75, 192, 192, 0)");

    // Secondo grafico: Attività utenti negli ultimi 5 anni
    new Chart(ctx2, {
        type: "line",
        data: {
            labels: [<?php echo implode(',', array_map(fn($y) => "'$y'", $years)); ?>],
            datasets: [{
                label: "Utenti Attivi",
                data: [<?php echo implode(',', $activeUsersPerYear); ?>],
                borderColor: "#4CAF50",
                backgroundColor: gradient3,
                fill: true,
                borderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointBackgroundColor: "#4CAF50",
                pointBorderColor: "#fff",
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 14, weight: "bold" } }
                },
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [5, 5], color: "rgba(0, 0, 0, 0.1)" },
                    ticks: { stepSize: 10, font: { size: 14 } }
                }
            },
            plugins: {
                legend: { position: "top", labels: { font: { size: 14 } } },
                tooltip: {
                    backgroundColor: "rgba(0, 0, 0, 0.8)",
                    bodyFont: { size: 14 },
                    callbacks: {
                        label: function(tooltipItem) {
                            return "Utenti Attivi: " + tooltipItem.raw;
                        }
                    }
                }
            },
            animation: { duration: 1500, easing: "easeInOutQuart" }
        }
    });
});






</script>


<?php include 'includes/footer.php'; ?>		