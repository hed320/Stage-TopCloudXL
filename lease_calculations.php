<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lease Calculations</title>
    <?php
    // Factors for calculating prizes
    $factor = array(
        array(24, 13.546, 13.301, 13.123, 13.062, 13.024, 13.002),
        array(36, 9.46, 9.196, 9.006, 8.941, 8.9, 8.876),
        array(48, 7.432, 7.155, 6.957, 6.889, 6.846, 6.822),
        array(60, 6.228, 5.94, 5.735, 5.665, 5.621, 5.596),
    );

    //When done calculating the array will be filled with the correct values
    $tot_Prices = array("Item name", "Buy price", "Sales price", "Factor", "Margin", "Insurance", "Monthly costs", "VAT",
        array("Quarterly", "24", "36", "48", "60"),
        array("Monthly", "24", "36", "48", "60"),
        array("Monthly Round", "24", "36", "48", "60"),
        array("Monthly Round inc VAT", "24", "36", "48", "60")
    );

    function calc_sales_price ($buyprice, $margin) {
        return $buyprice * $margin;
    };

    function get_factor ($buyprice) {
        if ($buyprice >= 1000 and $buyprice <= 2500) {
            return 1;
        } else if ($buyprice >= 2500 and $buyprice <= 5000) {
            return 2;
        } else if ($buyprice >= 5000 and $buyprice <=  10000) {
            return 3;
        } else if ($buyprice >= 10000 and $buyprice <= 25000) {
            return 4;
        } else if ($buyprice >= 25000 and $buyprice <=  50000) {
            return 5;
        } else if ($buyprice >= 50000) {
            return 6;
        } else {
            die("Factor not in range");
        };
    };

    //Quarterly (Sales price+Insurance+(Run time*Monthly costs))*Factor/100
    function calc_quarterly_prizes ($sales_Price, $factornum, $insurance, $monthly_Costs) {
        global $factor;
        global $tot_Prices;
        $quarterly = array();
        for ($x = 24; $x <= 60; ) {
            if ($x == 24) {
                $factortime = 0;
            } else if ($x == 36) {
                $factortime = 1;
            } else if ($x == 48) {
                $factortime = 2;
            } else if ($x == 60) {
                $factortime = 3;
            };
            $tot_Prices[3] = $factor[$factortime][$factornum];
            array_push($quarterly, round((($sales_Price + $insurance + ($x * $monthly_Costs)) * $tot_Prices[3] / 100), 2));
            $x += 12;
        };
        return $quarterly;
    };

    //Monthly (Quarterly/3)+1
    function calc_monthly ($quarterly_Array) {
        $monthly = array();
        foreach ($quarterly_Array as $value) {
            array_push($monthly, round($value / 3 + 1, 2));
        }
        return $monthly;
    };
    
    function calc_round_monthly ($monthly_Array) {
        $monthly_rounded = array();
        foreach ($monthly_Array as $value) {
            array_push($monthly_rounded, intval(round($value, 0)));
        }
        return $monthly_rounded;
    };

    function calc_round_monthly_vat ($monthly_Array, $vat) {
        $monthly_rounded_VAT = array();
        foreach ($monthly_Array as $value) {
            array_push($monthly_rounded_VAT, floatval(round($value * ((100 + $vat) / 100) , 2)));
        }
        return $monthly_rounded_VAT;
    };

    function calc_prizes ($itemname, $buyprice, $margin, $insurance, $monthly_Costs, $vat) {
        global $tot_Prices;
        $tot_Prices[0] = $itemname;
        $tot_Prices[1] = floatval($buyprice);
        $tot_Prices[2] = round(calc_sales_price($buyprice, $margin), 2);
        $tot_Prices[3] = get_factor($tot_Prices[2]);
        $tot_Prices[4] = floatval($margin);
        $tot_Prices[5] = floatval($insurance);
        $tot_Prices[6] = floatval($monthly_Costs);
        $tot_Prices[7] = intval($vat);
        $tot_Prices[8] = calc_quarterly_prizes($tot_Prices[2], $tot_Prices[3], $insurance, $monthly_Costs);
        $tot_Prices[9] = calc_monthly($tot_Prices[8]);
        $tot_Prices[10] = calc_round_monthly($tot_Prices[9]);
        $tot_Prices[11] = calc_round_monthly_vat($tot_Prices[9], $vat);

        //convert to json
        $filename = $itemname.'.json';
        $fp = fopen($filename, 'w');
        fwrite($fp, json_encode($tot_Prices));
        fclose($fp);
    };

    // If POST buyprice isset start calculation
    if (isset($_POST["buyprice"])) {
        calc_prizes($_POST["itemname"], $_POST["buyprice"], $_POST["margin"], $_POST["insurance"], $_POST["monthlycosts"], $_POST["vat"]);
    };
    var_dump($tot_Prices);
    ?>
</head>
<body>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <label for="itemname">Name: </label>
        <input type="text" id="itemname" name="itemname" required>
        <br>
        <label for="buyprice">Buy price: </label>
        <input type="number" id="buyprice" name="buyprice" step="0.01" required>
        <br>
        <label for="margin">Margin: </label>
        <input type="number" id="margin" name="margin" step="0.01" placeholder="150% = 1.5" required><br>
        <label for="insurance">Insurance: </label>
        <input type="number" id="insurance" name="insurance" step="0.01" required>
        <br>
        <label for="monthlycosts">Monthly costs: </label>
        <input type="number" id="monthlycosts" name="monthlycosts" step="0.01" required>
        <br>
        <label for="vat">VAT Percentage: </label>
        <input type="radio" id="vat" name="vat" value="17">17%
        <input type="radio" id="vat" name="vat" value="19">19%
        <input type="radio" id="vat" name="vat" value="21" checked>21%
        <br>
        <input type="submit" value="Calculate">
    </form>
    <br>
    <form>
            <label for="itemname">Name: </label>
            <input type="text" id="itemname" value="<?php echo $tot_Prices[0]; ?>" readonly>
            <br>
            <label for="buyprice">Buy price: </label>
            <input type="text" id="buyprice" value="<?php echo "€".$tot_Prices[1]; ?>" readonly>
            <br>
            <label for="salesprice">Sales price: </label>
            <input type="text" id="salesprice" value="<?php echo "€".$tot_Prices[2]; ?>" readonly>
            <br>
            <label for="margin">Margin: </label>
            <input type="text" id="margin" value="<?php echo $tot_Prices[4]; ?>" readonly>
            <br>
            <label for="insurance">Insurance: </label>
            <input type="text" id="insurance" value="<?php echo "€".$tot_Prices[5]; ?>" readonly>
            <br>
            <label for="monthlycosts">Monthly costs: </label>
            <input type="text" id="monthlycosts" value="<?php echo "€".$tot_Prices[6]; ?>" readonly>
            <br>
            <label for="vat">VAT percentage: </label>
            <input type="text" id="monthlycosts" value="<?php echo $tot_Prices[7]."%"; ?>" readonly>
            <br>
            <!--
            <label for="24quarterly">Quarterly 24 months :</label>
            <input type="text" id="24quarterly" value="<?php echo "€".$tot_Prices[8][0]; ?>" readonly>
            <br>
            <label for="36quarterly">Quarterly 36 months :</label>
            <input type="text" id="36quarterly" value="<?php echo "€".$tot_Prices[8][1]; ?>" readonly>
            <br>
            <label for="48quarterly">Quarterly 48 months :</label>
            <input type="text" id="48quarterly" value="<?php echo "€".$tot_Prices[8][2]; ?>" readonly>
            <br>
            <label for="60quarterly">Quarterly 60 months :</label>
            <input type="text" id="60quarterly" value="<?php echo "€".$tot_Prices[8][3]; ?>" readonly>
            <br>
            <label for="24months">24 Months :</label>
            <input type="text" id="24months" value="<?php echo "€".$tot_Prices[9][0]; ?>" readonly>
            <br>
            <label for="36months">36 Months :</label>
            <input type="text" id="36months" value="<?php echo "€".$tot_Prices[9][1]; ?>" readonly>
            <br>
            <label for="48months">48 Months :</label>
            <input type="text" id="48months" value="<?php echo "€".$tot_Prices[9][2]; ?>" readonly>
            <br>
            <label for="60months">60 Months :</label>
            <input type="text" id="60months" value="<?php echo "€".$tot_Prices[9][3]; ?>" readonly>
            -->
            <br>
            <label for="round24months">24 Months :</label>
            <input type="text" id="round24months" value="<?php echo "€".$tot_Prices[10][0]; ?>" readonly>
            <br>
            <label for="round36months">36 Months :</label>
            <input type="text" id="round36months" value="<?php echo "€".$tot_Prices[10][1]; ?>" readonly>
            <br>
            <label for="round48months">48 Months :</label>
            <input type="text" id="round48months" value="<?php echo "€".$tot_Prices[10][2]; ?>" readonly>
            <br>
            <label for="round60months">60 Months :</label>
            <input type="text" id="round60months" value="<?php echo "€".$tot_Prices[10][3]; ?>" readonly>
            <br>
            <label for="round24monthsvat">24 Months inc VAT:</label>
            <input type="text" id="round24monthsvat" value="<?php echo "€".$tot_Prices[11][0]; ?>" readonly>
            <br>
            <label for="round36monthsvat">36 Months inc VAT:</label>
            <input type="text" id="round36monthsvat" value="<?php echo "€".$tot_Prices[11][1]; ?>" readonly>
            <br>
            <label for="round48monthsvat">48 Months inc VAT:</label>
            <input type="text" id="round48monthsvat" value="<?php echo "€".$tot_Prices[11][2]; ?>" readonly>
            <br>
            <label for="round60monthsvat">60 Months inc VAT:</label>
            <input type="text" id="round60monthsvat" value="<?php echo "€".$tot_Prices[11][3]; ?>" readonly>
            <?php
                if (isset($_POST["itemname"]) and isset($_POST["buyprice"])) {
                    echo "<br>";
                    echo '<a href="'.$_POST["itemname"].'.json">JSON</a>';
                };
            ?>
    </form>
</body>
</html>