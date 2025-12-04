<?php
// task 1
$length = 10;
$width = 5;

$area = $length * $width;
$perimeter = 2 * ($length + $width);

echo "<h2>Rectangle Calculations</h2>";
echo "Length: " . $length . "units<br>";
echo "width: " . $width . "units<br>";
echo "Area: " . $area . "square units<br>";
echo "Perimeter" . $perimeter . "units<br>";

/* task 2 */

$amount = 1000;
$vat_rate = 0.15;

$vat_amount = $amount * $vat_rate;
$total_amount = $amount + $vat_amount;

echo "<h2>VAT Calculation</h2>";
echo "Original Amount: $" . number_format($amount, 2) . "<br>";
echo "VAT Rate: " . ($vat_rate * 100) . "%<br>";
echo "VAT Amount: $" . number_format($vat_amount, 2) . "<br>";
echo "Total Amount: $" . number_format($total_amount, 2) . "<br>";

/* task 3*/


echo "<h2>Odd or Even Number Checker</h2>";


$number = 7;

if ($number % 2 == 0) {
    echo "The number $number is <b>Even</b>";
} else {
    echo "The number $number is <b>Odd</b>";
}

// task 4
echo "<h2>Find Largest of Three Numbers</h2>";

$num1 = 15;
$num2 = 27;
$num3 = 8;

echo "Numbers: $num1, $num2, $num3<br>";

// Find the largest number using if-else
if ($num1 >= $num2 && $num1 >= $num3) {
    $largest = $num1;
} elseif ($num2 >= $num1 && $num2 >= $num3) {
    $largest = $num2;
} else {
    $largest = $num3;
}

echo "The largest number is: <b>$largest</b>";

// task 5

echo "<h2>Odd Numbers between 10 and 100</h2>";

// Using for loop
echo "<b>Using FOR loop:</b><br>";
for ($i = 10; $i <= 100; $i++) {
    if ($i % 2 != 0) {  // Check if number is odd
        echo $i . " ";
    }
}

echo "<br><br>";

// Using while loop
echo "<b>Using WHILE loop:</b><br>";
$num = 10;
while ($num <= 100) {
    if ($num % 2 != 0) {
        echo $num . " ";
    }
    $num++;
}

// task 6

echo "<h2>Search Element in Array </h2>";

// Define an array
$numbers = array(10, 20, 30, 40, 50, 60, 70);
$searchElement = 40;
$found = false;

echo "Array: ";
foreach ($numbers as $value) {
    echo $value . " ";
}
echo "<br>";

echo "Searching for element: $searchElement<br>";

// Search for the element
foreach ($numbers as $value) {
    if ($value == $searchElement) {
        $found = true;
        break;
    }
}

if ($found) {
    echo "Element $searchElement found in the array.<br>";
} else {
    echo "Element $searchElement not found in the array.<br>";
}

// task 7

echo "<h2>Pattern</h2>";

echo "<h2>Star Shape</h2>";

for ($i = 1; $i <= 3; $i++) {
    for ($j = 1; $j <= $i; $j++) {
        echo "* ";
    }
    echo "<br>";
}

echo "<br>";

echo "<h2>Number Shape</h2>";
$num = 1;
for ($i = 3; $i >= 1; $i--) {
    for ($j = 1; $j <= $i; $j++) {
        echo $num . " ";
        $num++;
    }
    echo "<br>";
}

echo "<br>";

echo "<h2>Letter Shape</h2>";
$ch = 'A';
for ($i = 1; $i <= 3; $i++) {
    for ($j = 1; $j <= $i; $j++) {
        echo $ch . " ";
        $ch++;
    }
    echo "<br>";
}



?>

