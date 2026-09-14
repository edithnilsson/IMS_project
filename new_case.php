<?php
//Insert data for a new case
//
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfecTracker</title>
    <style>
        img {
            display: block;
            margin: auto;
        }

        h1 {
            text-align: center;
        }
    </style>
</head>

<body>
    <img src="placeholder.png" width="600" height="300" class="top-logo">

    <h1>Log a Case</h1>

    <form>
        <label for="pathogen">Pathogen:</label>
        <select name="pathogen" id=pathogen">
            <option value="">Select Pathogen</option>
            <option value="staphylococcus_aureus">Staphylococcus aureus</option>
            <option value="sars_cov_2">SARS-CoV-2</option>
        </select>
    </form>
</body>

</html>