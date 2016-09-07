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

    //Profit margin
    $margin = 1.5;

    //Insurance
    $insurance = 349;

    //Additional monthly costs
    $monthly_Costs = 5;

    //When done calculating the array will be filled with the correct values
    $tot_Prices = array("Item name", "Item price", "Sales price", "Factor",
        array("Quarterly", "24", "36", "48", "60"),
        array("Monthly", "24", "36", "48", "60"),
        array("Montly Round", "24", "36", "48", "60"),
    );

    function calc_sales_price ($itemprice) {
        global $margin;
        return $itemprice * $margin;
    };

    function get_factor ($itemprice) {
        if ($itemprice >= 1000 and $itemprice <= 2500) {
            return 1;
        } else if ($itemprice >= 2500 and $itemprice <= 5000) {
            return 2;
        } else if ($itemprice >= 5000 and $itemprice <=  10000) {
            return 3;
        } else if ($itemprice >= 10000 and $itemprice <= 25000) {
            return 4;
        } else if ($itemprice >= 25000 and $itemprice <=  50000) {
            return 5;
        } else if ($itemprice >= 50000) {
            return 6;
        } else {
            die("Factor not in range");
        }
    };

    //Quarterly (Sales price+Insurance+(Run time*Monthly costs))*Factor/100
    function calc_quarterly_prizes ($sales_Price, $factornum) {
        global $insurance;
        global $monthly_Costs;
        global $factor;
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
            }
            $factorfloat = $factor[$factortime][$factornum];
            array_push($quarterly, round((($sales_Price + $insurance + ($x * $monthly_Costs)) * $factorfloat / 100), 2));
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
            array_push($monthly_rounded, round($value, 0));
        }
        return $monthly_rounded;
    };

    function calc_prizes ($itemname, $itemprice) {
        global $tot_Prices;
        $tot_Prices[0] = $itemname;
        $tot_Prices[1] = floatval($itemprice);
        $tot_Prices[2] = round(calc_sales_price($itemprice), 2);
        $tot_Prices[3] = get_factor($tot_Prices[2]);
        $tot_Prices[4] = calc_quarterly_prizes($tot_Prices[2], $tot_Prices[3]);
        $tot_Prices[5] = calc_monthly($tot_Prices[4]);
        $tot_Prices[6] = calc_round_monthly($tot_Prices[5]);

        //convert to json
        $filename = $itemname.'.json';
        $fp = fopen($filename, 'w');
        fwrite($fp, json_encode($tot_Prices));
        fclose($fp);
    };

    // If POST itemprice isset start calculation
    if (isset($_POST["itemprice"])) {
        if (!isset($_POST["itemname"])) {
            calc_prizes("Unknown itemname", $_POST["itemprice"]);
        } else {
            calc_prizes($_POST["itemname"], $_POST["itemprice"]);
        };
    };

    ?>
</head>
<body>
    <form action="lease calculations.php" method="post">
        <label for="itemname">Name: </label>
        <input type="text" id="itemname" name="itemname"><br>
        <label for="itemprice">Price: </label>
        <input type="number" id="itemprice" name="itemprice" step="0.01"><br>
        <input type="submit">
    </form>
    <br>
    <form>
            <label for="itemname">Item name: </label>
            <input type="text" id="itemname" value="<?php echo $tot_Prices[0]; ?>" readonly>
            <label for="itemprice">Item price: </label>
            <input type="text" id="itemprice" value="<?php echo "€".$tot_Prices[1]; ?>" readonly>
            <label for="salesprice">Sales price: </label>
            <input type="text" id="salesprice" value="<?php echo "€".$tot_Prices[2]; ?>" readonly>
            <br>
            <label for="quarterly24">Quarterly 24 months :</label>
            <input type="text" id="quarterly24" value="<?php echo "€".$tot_Prices[4][0]; ?>" readonly>
            <br>
            <label for="quarterly36">Quarterly 36 months :</label>
            <input type="text" id="quarterly36" value="<?php echo "€".$tot_Prices[4][1]; ?>" readonly>
            <br>
            <label for="quarterly48">Quarterly 48 months :</label>
            <input type="text" id="quarterly48" value="<?php echo "€".$tot_Prices[4][2]; ?>" readonly>
            <br>
            <label for="quarterly60">Quarterly 60 months :</label>
            <input type="text" id="quarterly60" value="<?php echo "€".$tot_Prices[4][3]; ?>" readonly>
            <br>
            <label for="monthly24">Monthly 24 months :</label>
            <input type="text" id="monthly24" value="<?php echo "€".$tot_Prices[5][0]; ?>" readonly>
            <br>
            <label for="monthly36">Monthly 36 months :</label>
            <input type="text" id="monthly36" value="<?php echo "€".$tot_Prices[5][1]; ?>" readonly>
            <br>
            <label for="monthly48">Monthly 48 months :</label>
            <input type="text" id="monthly48" value="<?php echo "€".$tot_Prices[5][2]; ?>" readonly>
            <br>
            <label for="monthly60">Monthly 60 months :</label>
            <input type="text" id="monthly60" value="<?php echo "€".$tot_Prices[5][3]; ?>" readonly>
            <br>
            <label for="roundmonthly24">Monthly 24 months round :</label>
            <input type="text" id="roundmonthly24" value="<?php echo "€".$tot_Prices[6][0]; ?>" readonly>
            <br>
            <label for="roundmonthly36">Monthly 36 months round :</label>
            <input type="text" id="roundmonthly36" value="<?php echo "€".$tot_Prices[6][1]; ?>" readonly>
            <br>
            <label for="roundmonthly48">Monthly 48 months round :</label>
            <input type="text" id="roundmonthly48" value="<?php echo "€".$tot_Prices[6][2]; ?>" readonly>
            <br>
            <label for="roundmonthly60">Monthly 60 months round :</label>
            <input type="text" id="roundmonthly60" value="<?php echo "€".$tot_Prices[6][3]; ?>" readonly>
            <?php
                if (isset($_POST["itemname"]) and isset($_POST["itemprice"])) {
                    echo "<br>";
                    echo '<a href="'.$_POST["itemname"].'.json">JSON</a>';
                }
                ?>
    </form>
</body>
</html>