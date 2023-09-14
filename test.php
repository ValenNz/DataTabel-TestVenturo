<?php

$selectedYear = isset($_GET['tahun']) ? $_GET['tahun'] : "";
$menuUrl = "http://tes-web.landa.id/intermediate/menu";
$transaksiUrl = "http://tes-web.landa.id/intermediate/transaksi?tahun=" . $selectedYear;

$menu = fetchData($menuUrl);
$transaksi = fetchData($transaksiUrl);
$menu = initializeMenuData($menu);
list($menu, $monthlyTotals, $yearlyTotal) = calculateTotals($menu, $transaksi);

function fetchData($url) {
    return json_decode(file_get_contents($url), true);
}

function initializeMenuData($menu) {
    $values = array_fill(0, 12, 0);
    foreach ($menu as &$item) {
        $item['value'] = $values;
        $item['totalHarga'] = 0;
    }
    return $menu;
}

function calculateTotals($menu, $transaksi) {
    $monthlyTotals = array_fill(0, 12, 0);
    $yearlyTotal = 0;

    foreach ($transaksi as $valueTrans) {
        $harga = $valueTrans['total'];
        $bulan = date('n', strtotime($valueTrans['tanggal']));

        foreach ($menu as &$menuItem) {
            if ($menuItem['menu'] === $valueTrans['menu']) {
                $menuItem['value'][$bulan - 1] += $harga;
                $menuItem['totalHarga'] += $harga;
            }
        }

        $monthlyTotals[$bulan - 1] += $harga;
        $yearlyTotal += $harga;
    }

    return [$menu, $monthlyTotals, $yearlyTotal];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }

        td,
        th {
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="card" style="margin: 2rem 0rem;">
            <div class="card-header">
                Venturo - Laporan penjualan tahunan per menu
            </div>
            <div class="card-body">
                <form action="" method="get">
                    <div class="row">
                        <div class="col-2">
                            <div class="form-group">
                                <select id="my-select" class="form-control" name="tahun">
                                    <option value="">Pilih Tahun</option>
                                    <?php for ($i = 2021; $i <= 2022; $i++): ?>
                                        <option value="<?= $i ?>" <?= $selectedYear == $i ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary">
                                Tampilkan
                            </button>
                        </div>
                    </div>
                </form>
                <hr>
                <?php if ($selectedYear): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" style="margin: 0;">
                            <thead>
                                <tr class="table-dark">
                                    <th rowspan="2" style="text-align:center;vertical-align: middle;width: 250px;">Menu</th>
                                    <th colspan="12" style="text-align: center;">Periode Pada <?= $selectedYear ?></th>
                                    <th rowspan="2" style="text-align:center;vertical-align: middle;width:75px">Total</th>
                                </tr>
                                <tr class="table-dark">
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <th style="text-align: center;width: 75px;"><?= date('M', strtotime("2022-$i-01")) ?></th>
                                    <?php endfor; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="table-secondary" colspan="14"><b>Makanan</b></td>
                                </tr>
                                <?php foreach ($menu as $item): ?>
                                    <?php if ($item['kategori'] === "makanan" || $item['kategori'] === "minuman"): ?>
                                        <tr>
                                            <td style="text-align: left;"><?= $item['menu'] ?></td>
                                            <?php foreach ($item['value'] as $nilai): ?>
                                                <td style="text-align: right;"><?= $nilai != 0 ? number_format($nilai) : "" ?></td>
                                            <?php endforeach; ?>
                                            <td style="text-align: right;"><b><?= number_format($item['totalHarga']) ?></b></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <tr>
                                    <td class="table-dark" colspan="1"><b>Total</b></td>
                                    <?php foreach ($monthlyTotals as $total): ?>
                                        <td class="table-dark" style="text-align: right;"><b><?= number_format($total) ?></b></td>
                                    <?php endforeach; ?>
                                    <td class="table-dark" style="text-align: right;" colspan="1"><b><?= number_format($yearlyTotal) ?></b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
